<?php

namespace WPWhales\Notifications;

use WPWhales\Bus\Queueable;
use WPWhales\Contracts\Queue\ShouldBeEncrypted;
use WPWhales\Contracts\Queue\ShouldQueue;
use WPWhales\Contracts\Queue\ShouldQueueAfterCommit;
use WPWhales\Database\Eloquent\Collection as EloquentCollection;
use WPWhales\Database\Eloquent\Model;
use WPWhales\Queue\InteractsWithQueue;
use WPWhales\Queue\SerializesModels;
use WPWhales\Support\Collection;

class SendQueuedNotifications implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The notifiable entities that should receive the notification.
     *
     * @var \WPWhales\Support\Collection
     */
    public $notifiables;

    /**
     * The notification to be sent.
     *
     * @var \WPWhales\Notifications\Notification
     */
    public $notification;

    /**
     * All of the channels to send the notification to.
     *
     * @var array
     */
    public $channels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions;

    /**
     * Indicates if the job should be encrypted.
     *
     * @var bool
     */
    public $shouldBeEncrypted = false;

    /**
     * Create a new job instance.
     *
     * @param  \WPWhales\Notifications\Notifiable|\WPWhales\Support\Collection  $notifiables
     * @param  \WPWhales\Notifications\Notification  $notification
     * @param  array|null  $channels
     * @return void
     */
    public function __construct($notifiables, $notification, array $channels = null)
    {
        $this->channels = $channels;
        $this->notification = $notification;
        $this->notifiables = $this->wrapNotifiables($notifiables);
        $this->tries = property_exists($notification, 'tries') ? $notification->tries : null;
        $this->timeout = property_exists($notification, 'timeout') ? $notification->timeout : null;
        $this->maxExceptions = property_exists($notification, 'maxExceptions') ? $notification->maxExceptions : null;

        if ($notification instanceof ShouldQueueAfterCommit) {
            $this->afterCommit = true;
        } else {
            $this->afterCommit = property_exists($notification, 'afterCommit') ? $notification->afterCommit : null;
        }

        $this->shouldBeEncrypted = $notification instanceof ShouldBeEncrypted;
    }

    /**
     * Wrap the notifiable(s) in a collection.
     *
     * @param  \WPWhales\Notifications\Notifiable|\WPWhales\Support\Collection  $notifiables
     * @return \WPWhales\Support\Collection
     */
    protected function wrapNotifiables($notifiables)
    {
        if ($notifiables instanceof Collection) {
            return $notifiables;
        } elseif ($notifiables instanceof Model) {
            return EloquentCollection::wrap($notifiables);
        }

        return Collection::wrap($notifiables);
    }

    /**
     * Send the notifications.
     *
     * @param  \WPWhales\Notifications\ChannelManager  $manager
     * @return void
     */
    public function handle(ChannelManager $manager)
    {
        $manager->sendNow($this->notifiables, $this->notification, $this->channels);
    }

    /**
     * Get the display name for the queued job.
     *
     * @return string
     */
    public function displayName()
    {
        return get_class($this->notification);
    }

    /**
     * Call the failed method on the notification instance.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function failed($e)
    {
        if (method_exists($this->notification, 'failed')) {
            $this->notification->failed($e);
        }
    }

    /**
     * Get the number of seconds before a released notification will be available.
     *
     * @return mixed
     */
    public function backoff()
    {
        if (! method_exists($this->notification, 'backoff') && ! isset($this->notification->backoff)) {
            return;
        }

        return $this->notification->backoff ?? $this->notification->backoff();
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime|null
     */
    public function retryUntil()
    {
        if (! method_exists($this->notification, 'retryUntil') && ! isset($this->notification->retryUntil)) {
            return;
        }

        return $this->notification->retryUntil ?? $this->notification->retryUntil();
    }

    /**
     * Prepare the instance for cloning.
     *
     * @return void
     */
    public function __clone()
    {
        $this->notifiables = clone $this->notifiables;
        $this->notification = clone $this->notification;
    }
}
