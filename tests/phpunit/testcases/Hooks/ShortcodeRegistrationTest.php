<?php

namespace Tests\Hooks;

use WPWCore\Exceptions\WPWException;
use WPWCore\Hooks\ShortCodeInterface;
use WPWCore\Providers\HooksServiceProvider;
use function WPWCore\config;

class ShortcodeRegistrationTest extends \WP_UnitTestCase
{

    public function set_up()
    {

        parent::set_up();


    }


    public function test_shortcode_registered()
    {
        global $shortcode_tags;
        $this->app["config"]->set("shortcodes", [Shortcode::class]);

        $this->app->register(HooksServiceProvider::class);
        do_action("init");
        $this->assertArrayHasKey("xyz", $shortcode_tags);

        $this->assertInstanceOf(Shortcode::class, $shortcode_tags["xyz"][0]);
        $this->assertEquals($shortcode_tags["xyz"][1], "render");
    }

    public function test_shortcode_output()
    {
        $this->app["config"]->set("shortcodes", [Shortcode::class]);

        $this->app->register(HooksServiceProvider::class);
        do_action("init");

        $this->assertSame(do_shortcode("[xyz]"), "123");

    }

    public function test_failed_shortcode_registered()
    {

        $this->expectException(WPWException::class);
        $this->app["config"]->set("shortcodes", [FailShortCode::class]);

        $this->app->register(HooksServiceProvider::class);
        do_action("init");
    }

}

class FailShortCode
{

}

class Shortcode implements ShortCodeInterface
{

    public function getName(): string
    {
        return "xyz";
    }

    public function render(): string
    {
        return 123;
    }
}
