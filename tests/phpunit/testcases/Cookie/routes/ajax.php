<?php


$router->get("/test_ajax_route", [
    "as" => "ajax_route_name",
    function () {

        \WPWCore\app("cookie")->queue("test_cookie", "test_value");
        return "1";
    }
]);


