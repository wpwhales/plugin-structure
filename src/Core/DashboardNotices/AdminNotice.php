<?php

namespace WPWCore\DashboardNotices;

use WPWhales\Support\Facades\Validator;
use function WPWCore\app;
use function WPWCore\asset;

class AdminNotice
{
    public const TRANSIENT_KEY = 'wpwcore_notices';
    public const DISMISS_ACTION = 'wpwcore_notice_dismiss';

    public static function init()
    {
        add_action('admin_notices', function () {
            static::outputNotices();
        });

        add_action('wp_ajax_' . static::DISMISS_ACTION, function () {
            static::ajaxDismiss();
        });

        add_action("admin_footer", function () {


            self::script();
        });
    }

    /**
     * Get notices from transient.
     * @return array
     */
    protected static function getNotices(): array
    {
        return get_transient(static::TRANSIENT_KEY) ?: [];
    }

    /**
     * Set notices to transient.
     * @param array $notices
     * @return bool
     */
    protected static function updateNotices(array $notices): bool
    {
        return set_transient(static::TRANSIENT_KEY, $notices, YEAR_IN_SECONDS);
    }

    /**
     * Delete all notices from transient.
     * @return bool
     */
    public static function clearAll(): bool
    {
        return delete_transient(static::TRANSIENT_KEY);
    }

    /**
     * Process an AJAX request to dismiss this notice.
     *
     * @internal
     */
    protected static function ajaxDismiss()
    {
        check_ajax_referer(static::DISMISS_ACTION);

        if (!is_user_logged_in()) {
            wp_die('Access denied. You need to be logged in to dismiss notices.');
            return;
        }

        $validator = Validator::make($_POST, ["notice_id" => "string|required"]);


        if ($validator->fails()) {
            wp_send_json_error(["success"=>false], 422);
        }
        $data = $validator->validated();

        static::removePersistentNotice($data["notice_id"]);


        wp_send_json(["success"=>true],200);
    }

    /**
     * @param string $type
     * @param string $message
     * @param bool $isDismissible
     * @param null $id
     */
    protected static function addNotice(string $type, string $message, bool $isDismissible = true, $id = null)
    {
        $noticeToBeAdded = compact('type', 'message', 'isDismissible', 'id');

        // update the notice with the given id if possible
        $updated = false;
        $notices = static::getNotices();
        foreach ($notices as &$notice) {
            if ($notice['id'] && $notice['id'] === $id) {
                $notice = $noticeToBeAdded;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $notices[] = $noticeToBeAdded;
        }

        static::updateNotices($notices);
    }

    public static function success($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('success', $message, $isDismissible, $noticeId);
    }

    public static function error($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('error', $message, $isDismissible, $noticeId);
    }

    public static function warning($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('warning', $message, $isDismissible, $noticeId);
    }

    public static function info($message, $isDismissible = true, $noticeId = null)
    {
        static::addNotice('info', $message, $isDismissible, $noticeId);
    }

    /**
     * Out put all the messages added.
     */
    protected static function outputNotices()
    {
        foreach (static::getNotices() as $notice) {
            $class = 'notice notice-' . $notice['type'] . ($notice['isDismissible'] ? ' is-dismissible' : '');
            $message = $notice['message'];
            $noticeId = $notice['id'];
            $dataNoticeId = $noticeId ? "data-notice-id=\"{$noticeId}\"" : '';
            $dismissNonce = $noticeId ? wp_create_nonce(static::DISMISS_ACTION) : null;
            $dataDismissNonce = $noticeId ? "data-dismiss-nonce=\"{$dismissNonce}\"" : '';
            echo "<div class=\"{$class}\" {$dataNoticeId} {$dataDismissNonce}><p>{$message}</p></div>";
        }

        static::removeOneTimeNotice();
    }

    protected static function removeOneTimeNotice()
    {
        $notices = array_filter(static::getNotices(), function ($notice) {
            return $notice['id'];
        });

        static::updateNotices($notices);
    }

    protected static function removePersistentNotice(string $noticeId)
    {
        $notices = [];

        foreach (static::getNotices() as $notice) {
            if (isset($notice['id']) && $notice['id'] === $noticeId) {
                continue;
            }

            $notices[] = $notice;
        }

        static::updateNotices($notices);
    }


    public static function script()
    {
        ?>
        <script>
            jQuery(document).ready(() => {
                jQuery(function ($) {
                    $('.notice[data-dismiss-nonce]').on('click', '.notice-dismiss', function () {
                        var $notice = $(this).closest('.notice'),
                            nonce = $notice.data('dismiss-nonce'),
                            notice_id = $notice.data('notice-id');

                        $.post(
                            ajaxurl,
                            {
                                "action": 'wpwcore_notice_dismiss',
                                "_ajax_nonce": nonce,
                                "notice_id": notice_id
                            }
                        );
                    });
                });
            })
        </script>
        <?php
    }
}