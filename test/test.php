<?php

$loader = require_once 'vendor/autoload.php';

$http = new \obray\oHTTP();
$http->addRequest("http://nate.hq-beta.test/m/test/oTest/testPost/",\obray\oHTTP::GET,NULL,NULL,function($data){
    print_r($data);
    exit();
});
$responses = $http->send();
//print_r($responses);
exit();
//echo $response;
print_r($response->getHeaders());
$body = $response->getBody();
$body = json_decode($body);
print_r($body);
