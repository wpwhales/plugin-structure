<?php

namespace Tests\Options;


use WPWCore\Options\OptionsField;
use WPWCore\Options\OptionsPage;
use WPWCore\Options\OptionsSection;

class OptionsHandlerTest extends \WP_UnitTestCase
{


    public function testOptions()
    {
        global $wp_filter;
        $user_id = $this->factory()->user->create();

        $user = get_userdata($user_id);
        $user->add_role("administrator");
        wp_set_current_user($user_id);

        $optionsPage = new OptionsPage([
            'menuSlug'    => 'my_options_page',
            'menuTitle'   => 'My Options Page',
            'pageTitle'   => 'My Options Page',
            'iconUrl'     => 'dashicons-welcome-learn-more',
            'optionGroup' => 'my_options_page',
            'optionName'  => 'my_options',
            'capability'  => 'manage_categories',
            'sections'    => [
                [
                    'id'          => 'section-id',
                    'title'       => 'Section title',
                    'description' => 'Section Description',
                    'fields'      => [
                        [
                            'id'          => 'my-avatar',
                            'type'        => 'media',
                            'title'       => 'Avatar',
                            'description' => 'Choose an image for your avatar.'
                        ],
                        [
                            'id'    => 'my-email',
                            'type'  => 'email',
                            'title' => 'E-mail',
                        ],
                        [
                            'id'         => 'my-nice-name',
                            'type'       => 'text',
                            'title'      => 'Nice name',
                            'attributes' => [
                                'placeholder' => 'your nice name',
                                'maxlength'   => 10,
                                'class'       => 'regular-text'
                            ],
                        ],
                        [
                            'id'    => 'my-description',
                            'type'  => 'textarea',
                            'title' => 'About Me',
                        ],
                    ]
                ]
            ],
            'helpTabs'    => [
                [
                    'title'   => 'tab-1',
                    'content' => '<p>description here</p>',
                ],
                [
                    'title'   => 'tab-2',
                    'content' => '<p>description here</p>',
                ]
            ],
            'scripts'     => ['https://unpkg.com/vue/dist/vue.js'],
            'styles'      => ['/my-css.css'],
        ]);

        $optionsPage->register();


        $this->runOptionsPageHooks($optionsPage->hookSufix,$optionsPage->menuTitle());

        ob_start();
        $optionsPage->render();
        $content =ob_get_clean();



    }


    private function runOptionsPageHooks($pageHook, $screenTitle)
    {
        global $wp_filter, $title;
        set_current_screen($pageHook);
        $title = $screenTitle;
        foreach ($wp_filter["admin_menu"]->callbacks[10] as $instance) {
            if (is_array($instance["function"]) && is_a($instance["function"][0], OptionsPage::class)) {

                $i = $instance["function"][0];
                $method = $instance["function"][1];
                $i->{$method}();
            }
        }
        foreach ($wp_filter["admin_init"]->callbacks[10] as $instance) {
            if (is_array($instance["function"]) && (is_a($instance["function"][0], OptionsPage::class) ||
                    is_a($instance["function"][0], OptionsSection::class)
                    ||
                    is_a($instance["function"][0], OptionsField::class)
                )) {

                $i = $instance["function"][0];
                $method = $instance["function"][1];
                $i->{$method}();
            }
        }

    }
}



