<?php




$router->get("/test_ajax_route", [
    "as"=>"ajax_route_name",
    function () {
    return "1";
}]);




$router->get("/test_ajax_signed_route", [
    "as"=>"ajax_signed_route_name",
    "middleware"=>[\WPWCore\Routing\Middleware\ValidateSignature::class],
    function () {
        return "123";
    }]);


$router->get("/test_ajax_route_binding/{event}/edit", [
    "as"=>"test_ajax_route_binding",
    function ($request,$event) {
        return "123";
    }]);