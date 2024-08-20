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

    private function normalizeArray($array)
    {

        return array_unique(array_map(function ($className) {
            // Remove leading backslashes
            $className = ltrim($className, '\\');

            // Convert ::class constant to string if applicable
            if (defined($className)) {
                $className = constant($className);
            }

            return $className;
        }, $array));
    }

    public function boot()
    {
        $hooks = $this->normalizeArray(config("hooks", []));
        $widgets = $this->normalizeArray(config("widgets", []));



        add_action("init",function(){
            $shortcodes = $this->normalizeArray(config("shortcodes", []));
            foreach ($shortcodes as $sc) {
                $instance = new $sc;
                if(!method_exists($instance,"render")){
                    throw new WPWException("Shortcode {$sc} is missing the render method");
                }

                if(!method_exists($instance,"getName")){
                    throw new WPWException("Shortcode {$sc} is missing the getName method");
                }
                add_shortcode($instance->getName(), [$instance, "render"]);

            }
        });

        foreach ($hooks as $class) {

            $obj = new $class;
            $this->app->registerHook($obj);

        }

        add_action('widgets_init', function () use ($widgets) {

            foreach ($widgets as $w) {


                if (!is_subclass_of($w, "WP_Widget")) {
                    throw new WPWException("Widget class should extend \WP_Widget class");
                }
                register_widget($w);
            }
        });




    }


}
