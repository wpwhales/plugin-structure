<?php

namespace Tests\Routing\View;

use WPWhales\Http\Request;

class TestController extends \WPWCore\Routing\Controller
{


    public function view_in_response(Request $request)
    {

        return \WPWCore\view("test");


    }

    public function view_with_method(Request $request)
    {



        return \WPWCore\view("test")->with([
            "data_1"=>[1,2,3],
            "data_2"=>"something"
        ]);


    }

}