<?php

namespace Tests\Hooks;

use WPWCore\Providers\HooksServiceProvider;
use function WPWCore\config;

class HooksRegistrationTest extends \WP_UnitTestCase
{

    public function set_up()
    {
        parent::set_up();

        $this->app["config"]->set("hooks",[SampleHook::class]);

        $this->app->register(HooksServiceProvider::class);


    }

    public function test_hooks_registered()
    {

       $this->assertTrue(has_action("sample_hook"));

    }

    public function test_hook_output(){

        $this->assertFalse(boolval(did_action("sample_hook")));
        ob_start();
        do_action("sample_hook");
        $this->assertSame(ob_get_clean(),'123');


        $this->assertTrue(boolval(did_action("sample_hook")));

    }

}


class SampleHook
{


    public function __construct()
    {

        add_action("sample_hook", [$this, "test"]);
    }

    public function test()
    {

        echo  123;
    }
}
