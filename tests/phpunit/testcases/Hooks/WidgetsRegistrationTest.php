<?php

namespace Tests\Hooks;

use WPWCore\Exceptions\WPWException;
use WPWCore\Providers\HooksServiceProvider;
use function WPWCore\config;

class WidgetsRegistrationTest extends \WP_UnitTestCase
{

    public function set_up()
    {

        parent::set_up();


    }

    public function test_widgets_registered()
    {


        global $wp_widget_factory, $wp_filter;

        $this->app["config"]->set("widgets", [SampleWidget::class]);

        $this->app->register(HooksServiceProvider::class);

        do_action("widgets_init");
        $this->assertArrayHasKey(SampleWidget::class, $wp_widget_factory->widgets);

        $this->assertInstanceOf(SampleWidget::class, $wp_widget_factory->widgets[SampleWidget::class]);
    }


    public function test_wrong_widget_registered()
    {
        $this->expectException(WPWException::class);
        $this->expectExceptionMessage("Widget class should extend \WP_Widget class");
        $this->app["config"]->set("widgets", [WrongClass::class]);

        $this->app->register(HooksServiceProvider::class);
        do_action("widgets_init");


    }

    public function test_wrong_widget_without_constructor_registered()
    {
        $this->expectException(\ArgumentCountError::class);

        $this->app["config"]->set("widgets", [WrongClass2::class]);

        $this->app->register(HooksServiceProvider::class);
        do_action("widgets_init");


    }

}

class WrongClass
{

}

class WrongClass2 extends \WP_Widget
{

}

class SampleWidget extends \WP_Widget
{


    public function __construct()
    {

        parent::__construct(false, "sample widget");

    }

}
