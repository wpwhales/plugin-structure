<?php

namespace Tests\Options;


use Mockery\Mock;
use WPWCore\Assets\Asset\Asset;
use WPWCore\Assets\Manifest;
use WPWCore\Options\OptionsField;
use WPWCore\Options\OptionsPage;
use WPWCore\Options\OptionsSection;
use WPWCore\Testing\DatabaseMigrations;
use Mockery as m;
use function WPWCore\base_path;

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
            'scripts'     => ['/my-js.js'],
            'styles'      => ['/my-css.css'],
        ]);


        $optionsPage->register();




        //mock the assets.manifest class and check if a method named as asset is getting hit or not.

        $assetsInstance = $this->getMockBuilder(Manifest::class)->disableOriginalConstructor()->onlyMethods(["asset"])->getMock();

        $directory_root = dirname(base_path(),2)."/";
        $asset = new Asset($directory_root,$directory_root);
        $assetsInstance->expects($this->exactly(1))->method("asset")->will($this->returnValue($asset));

        $this->app["assets.manifest"]= $assetsInstance;
        $this->runOptionsPageHooks("CUSTOM_PLUGIN_page_".$optionsPage->menuSlug(), $optionsPage->menuTitle());

        $this->assertTrue(in_array("/my-css.css",wp_styles()->queue));
        $this->assertTrue(in_array("/app/css/sharedStyle.css",wp_styles()->queue));
        $this->assertTrue(in_array("/app/css/media.css",wp_styles()->queue));

        $this->assertTrue(in_array("/app/js/MediaFieldGenerator.js",wp_scripts()->queue));
        $this->assertTrue(in_array("/my-js.js",wp_scripts()->queue));

        ob_start();
        $optionsPage->render();
        $content = ob_get_clean();

        $this->assertStringContainsString('<input type=\'hidden\' name=\'option_page\' value=\'my_options_page\' />', $content);
        $this->assertMatchesRegularExpression('/<input type="hidden" id="_wpnonce" name="_wpnonce" value="[^"]+" \/>/', $content);
        $this->assertStringContainsString('<div class="js-laraish-media-field laraish-media-field ">', $content);
        $this->assertStringContainsString('<h2>Section title</h2>', $content);
        $this->assertStringContainsString('<p>Section Description</p>', $content);

        $this->assertStringContainsString('<input type="email" class="regular-text" name="my_options[my-email]" value="">', $content);

        $this->assertStringContainsString('<input type="text" class="regular-text" placeholder="your nice name" maxlength="10" name="my_options[my-nice-name]" value="">', $content);
        $this->assertStringContainsString('<textarea cols="60" rows="5" name="my_options[my-description]"></textarea>', $content);


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
        do_action("admin_enqueue_scripts",$pageHook);

    }
}



