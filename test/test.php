<?php

$loader = require_once 'vendor/autoload.php';

$http = new \obray\oHTTP("https://hq.maloufsleep.com/m/products/oProducts/get/?start=0&rows=100");
$response = $http->send();
echo $http->getRequest();
echo $response;
print_r($response->getHeaders());
$body = $response->getBody();
$body = json_decode($body);
print_r($body);
