<?php



$router->get("/test_plain_response", function () {
    return "test_plain_response";
});
$router->post("/test_plain_response", function () {
    return "test_plain_response";
});

$router->get("/test_json_response", function () {
    return ["test_json_response"];
});
$router->post("/test_json_response", function () {
    return ["test_json_response"];
});

$router->get("/test_csv_content", function () {
    $fileName = 'tasks_web.csv';

    $headers = array(
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    );

    $columns = array('c', 'd');

    $callback = function () use ($columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        fputcsv($file, array(4,5));


        fclose($file);
    };
    return (new \Symfony\Component\HttpFoundation\StreamedResponse($callback, 200, $headers));
});

$router->get("/test_binary_file_response", function () {
    $headers = array(
        'Content-Type: text/plain',
    );
    $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse(__DIR__ . "/download-file-web.txt", 200, $headers, true, 'attachment');


    return $response;
});

$router->get("/test_421_response", function () {


    throw new \WPWCore\Exceptions\WPWException("error in web route", 421);

});

$router->get("/test_500_response", function () {


    throw new \WPWCore\Exceptions\WPWException("error in web route", 500);

});

$router->get("/current_logged_in_user", function () {


    $user = wp_get_current_user();
    return ["ID"=>$user->ID];

});
