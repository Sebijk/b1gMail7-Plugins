<?php

$google = "www.google.com";
$lang = $_GET['lang'];
$path = "/tbproxy/spell?lang=$lang";
$data = file_get_contents('php://input');


$curl_handle = curl_init();

curl_setopt($curl_handle, CURLOPT_URL, 'https://'.$google.$path);
curl_setopt($curl_handle, CURLOPT_PORT, 443);
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
$curl_headers = array('Host: '.$google,
'Content-Type: application/x-www-form-urlencoded',
'Content-Length: '.strlen($data),
'Connection: Close');
curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $curl_headers);
curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
curl_setopt($curl_handle, CURLOPT_POSTFIELDSIZE, strlen($data));

curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);


$store = curl_exec($curl_handle);

if (0 == curl_errno($curl_handle)) {
curl_close($curl_handle);
}


print $store;
?>