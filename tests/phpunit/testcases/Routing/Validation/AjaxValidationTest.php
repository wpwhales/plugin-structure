<?php

namespace Tests\Routing\Validation;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Routing\TraitRoutingHandler;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;


/**
 *  !!!! ONLY FOR AJAX CALL !!!!
 *
 * Tests for the Ajax calls to save and get sos stats.
 * For speed, non ajax calls of class-ajax.php are tested in test-ajax-others.php
 * Ajax tests are not marked risky when run in separate processes and wp_debug
 * disabled. But, this makes tests slow so non ajax calls are kept separate
 *
 *
 */
class AjaxValidationTest extends \WP_Ajax_UnitTestCase
{



    public function test_simple_data_validation_with_invalid_data()
    {

        $this->app->router->get("/simple_data_validation",["uses"=>TestAjaxValidationController::class."@simple_data_validation"]);


        $response = $this->adminAjaxCall("GET", "/simple_data_validation");

        $response->assertJsonMissingValidationErrors([
            "text_field"
        ]);

        $response->assertStatus(422);



    }

    public function test_simple_data_validation_with_valid_data()
    {

        $this->app->router->get("/simple_data_validation",["uses"=>TestAjaxValidationController::class."@simple_data_validation"]);


        $response = $this->adminAjaxCall("GET", "/simple_data_validation",["text_field"=>123]);

        $response->assertStatus(200);
        $response->assertContent("success");



    }





}

