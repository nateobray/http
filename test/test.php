<?php

$loader = require_once 'vendor/autoload.php';

$http = new \obray\oHTTP();
$http->addRequest("https://httpbin.org/get",\obray\oHTTP::GET);
$responses = $http->send();
