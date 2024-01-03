<?php

namespace Tests\Url;


use WPWCore\Http\Request;

class UrlBaseFunctionsTest extends \WP_UnitTestCase
{


    public function test_check_to_function_for_url()
    {

        $this->assertEquals($this->app["url"]->to("/something_unique"), site_url("/something_unique"));
    }


    public function test_check_full_function()
    {


        $this->app["request"] = Request::capture();

        $this->assertEquals($this->app["url"]->full(),site_url());

    }
    public function test_check_current_function()
    {


        $this->app["request"] = Request::capture();

        $this->assertEquals($this->app["url"]->current(),site_url());

    }

    public function test_check_secure_function()
    {


        $this->app["request"] = Request::capture();

        $this->assertEquals($this->app["url"]->secure("/"),site_url("","https"));

    }
}
