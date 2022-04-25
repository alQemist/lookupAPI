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
$curl_connection = curl_init($url);

foreach ($input_mapping as $k => $v) {
    $post_data->$v = $GLOBALS[$k];
}
foreach ($input_constants as $k => $v) {
    $post_data->$k = $v;
}

function getCookie($url){
    $cookie = "COOKIE.TXT";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_exec($ch);
    curl_close($ch);
    return($cookie);
}

function prepareCurlOptions($url, $post_data, $headers,$cookie)
{

    $url_info = parse_url($url);
    $baseurl = $url_info['host'];
    $userAgent = "'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)'";
    $boundary = uniqid();

    $headers[] = "content-type: multipart/form-data; boundary=---" . $boundary;
    $headers[] = "User-Agent: " . $userAgent;
    $headers[] = "Accept: */*";
    $headers[] = "X-Requested-With: XMLHttpRequest";
    $headers[] = "Referer:" . $baseurl;
    $headers[] = "Accept-Language: pt-BR,en-US;q=0.7,en;q=0.3";

    //$post_fields = [];
    $post_fields = "-----" . $boundary . "\r\n";

    $separate = count($post_data);
    foreach ($post_data as $k => $v) {
        $post_fields .= "Content-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n-----" . $boundary;

        // add \r\n separator after each field, except last one
        if (--$separate > 0) {
            $post_fields .= "\r\n";

        }
        //$post_fields[] = $k."=".$v;
    }
    $post_fields .= "--";
    //$post_fields = implode("&",$post_fields);


    return array(
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        //CURLOPT_COOKIEFILE, $cookie,
        //CURLOPT_COOKIEJAR, $cookie,
        CURLOPT_POSTFIELDS => $post_fields,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
    );
}

$cookie = getCookie($url);

curl_setopt_array($curl_connection, prepareCurlOptions($url, $post_data, $headers,$cookie));

$result = curl_exec($curl_connection);

//show information regarding the request
print_r(curl_getinfo($curl_connection));
//echo curl_errno($curl_connection) . '-' .  curl_error($curl_connection);

print_r($result);

//close the connection
curl_close($curl_connection);
