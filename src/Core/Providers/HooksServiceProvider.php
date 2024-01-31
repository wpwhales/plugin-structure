<?php

namespace WPWCore\Providers;

use WPWCore\Exceptions\WPWException;
use WPWCore\Hooks\ShortCodeInterface;
use WPWhales\Support\ServiceProvider;
use function WPWCore\config;

class HooksServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }


    public function boot()
    {
        $hooks = config("hooks", []);
        $widgets = config("widgets", []);
        $shortcodes = config("shortcodes", []);


        foreach ($hooks as $class) {

            new $class;

        }
        add_action('widgets_init', function () use ($widgets) {

            foreach ($widgets as $w) {


                if (!is_subclass_of($w, "WP_Widget")) {
                    throw new WPWException("Widget class should extend \WP_Widget class");
                }
                register_widget($w);
            }
        });


        foreach ($shortcodes as $sc) {
            $instance = new $sc;

            if (!($instance instanceof ShortCodeInterface)) {
                throw new WPWException("Shortcode class must implements the ShortCodeInterface");
            }

            add_shortcode($instance->getName(), [$instance, "render"]);

        }

    }


}
