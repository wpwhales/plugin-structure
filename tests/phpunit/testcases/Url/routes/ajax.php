<?php




$router->get("/test_ajax_route", [
    "as"=>"ajax_route_name",
    function () {
    return "1";
}]);