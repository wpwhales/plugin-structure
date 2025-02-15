<?php


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WPWCore\Exceptions\WPWException;


$router->get("/test_plain_response", function () {
    return "1";
});
$router->post("/test_plain_response", function () {
    return "2";
});

$router->get("/test_json_response", function () {
    return [1, 2, 3];
});
$router->post("/test_json_response", function () {
    return [4, 5, 6];
});

$router->get("/test_csv_content", function () {
    $fileName = 'tasks.csv';

    $headers = array(
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    );

    $columns = array('a', 'b');

    $callback = function () use ($columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        fputcsv($file, array(1, 2, 3));


        fclose($file);
    };
    return (new StreamedResponse($callback, 200, $headers));
});

$router->get("/test_binary_file_response", function () {
    $headers = array(
        'Content-Type: text/plain',
    );
    $response = new BinaryFileResponse(__DIR__ . "/download-file-ajax.txt", 200, $headers, true, 'attachment');


    return $response;
});

$router->get("/test_421_response", function () {


    throw new WPWException("error in route", 421);

});

$router->get("/test_500_response", function () {


    throw new WPWException("error in route", 500);

});

$router->get("/current_logged_in_user", function () {


    $user = wp_get_current_user();
    return ["ID"=>$user->ID];

});