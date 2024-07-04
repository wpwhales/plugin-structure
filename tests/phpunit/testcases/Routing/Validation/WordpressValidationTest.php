<?php

namespace Tests\Routing\Validation;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Routing\TraitRoutingHandler;
use WPWCore\Exceptions\WPWException;
use WPWCore\Exceptions\WPWExceptionInterface;
use WPWhales\Testing\TestResponse;


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
class WordpressValidationTest extends \WP_UnitTestCase
{


    public function set_up()
    {
        parent::set_up();

        $paths = \WPWCore\config("view.paths");
        $paths[] = __DIR__;

        $config = \WPWCore\app("config");
        $config->set("view.paths", $paths);
    }

    public function test_simple_data_validation_with_invalid_data()
    {

        $this->app->wordpressRouter->post("/simple_data_validation",["uses"=>TestAjaxValidationController::class."@simple_data_validation"]);

        $this->app["session"]->start();

        $this->app["session"]->setPreviousUrl("/simple_data_validation");

        $response = $this->wordpressCall("POST", "/simple_data_validation");

        /**
         * @var $response TestResponse
         */
        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $response->assertLocation(\WPWCore\url("/simple_data_validation"));




    }

    public function test_simple_data_validation_with_valid_data()
    {

        $this->app->wordpressRouter->post("/simple_data_validation",["uses"=>TestAjaxValidationController::class."@simple_data_validation"]);


        $response = $this->wordpressCall("POST", "/simple_data_validation",["text_field"=>123]);

        $response->assertStatus(200);
        $response->assertContent("success");



    }






}

