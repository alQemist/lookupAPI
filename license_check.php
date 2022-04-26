<?php

include_once("stdObject.php");

$lookupdata = file_get_contents("data/data.json");
$jsonObj = json_decode($lookupdata, true);

foreach ($_POST as $key => $value) {
    ${$key} = $value;
}
foreach ($_GET as $key => $value) {
    ${$key} = $value;
}
//data object to submit
$post_data = new stdObject();

// required input vars
$headers = [];
$type = strtolower($type);
$state = strtoupper($state);
$url = $jsonObj[$state]['url'][$type];
$input_mapping = $jsonObj[$state]['input_mapping'];
$input_constants = $jsonObj[$state]['input_constants'];
$curl_connection = curl_init();

foreach ($input_mapping as $k => $v) {
    $post_data->$v = $GLOBALS[$k];
}
foreach ($input_constants as $k => $v) {
    $post_data->$k = $v;
}

curl_setopt_array($curl_connection, prepareCurlOptions($url, $post_data, $headers));

$response = curl_exec($curl_connection);

//show information regarding the request
print_r(curl_getinfo($curl_connection));
//echo curl_errno($curl_connection) . '-' .  curl_error($curl_connection);

echo $response;

//close the connection
curl_close($curl_connection);


function prepareCurlOptions($url, $post_data, $headers)
{
    return [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($post_data),
        CURLOPT_HTTPHEADER => array_merge($headers, [
            "Content-Type: application/x-www-form-urlencoded",
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
    ];
}
