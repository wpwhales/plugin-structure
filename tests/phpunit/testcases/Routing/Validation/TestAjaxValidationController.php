<?php

namespace Tests\Routing\Validation;

use WPWhales\Http\Request;

class TestAjaxValidationController extends \WPWCore\Routing\Controller
{


    public function simple_data_validation(Request $request)
    {

        $this->validate($request, [
            "text_field" => ["required"]
        ]);

        return "success";


    }
}