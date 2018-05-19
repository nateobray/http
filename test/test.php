<?php

$loader = require_once 'vendor/autoload.php';

$http = new \obray\oHTTP("http://nate.hq-beta.test/m/products/oProducts/get/?start=0&rows=100");
echo $http->getRequest();
$response = $http->send();
echo $response;
print_r($response->getHeaders());
$body = $response->getBody();
$body = json_decode($body);
print_r($body);
