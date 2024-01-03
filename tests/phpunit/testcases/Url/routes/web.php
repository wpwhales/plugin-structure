<?php


$router->get("/test_web_route", [
    "as" => "test_web_route",
    function () {
        return "1";
    }
]);


