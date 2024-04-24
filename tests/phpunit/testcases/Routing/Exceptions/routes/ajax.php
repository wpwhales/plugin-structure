<?php


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WPWCore\Exceptions\WPWException;


$router->get("/test_421_response", function () {


    throw new WPWException("error in route", 421);

});

$router->get("/test_500_response", function () {


    throw new WPWException("error in route", 500);

});
