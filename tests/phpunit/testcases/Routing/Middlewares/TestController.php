<?php

namespace Tests\Routing\Middlewares;

use WPWhales\Http\Request;

class TestController extends \WPWCore\Routing\Controller
{


    public function middleware_check(Request $request)
    {



        return get_current_user_id();


    }
}