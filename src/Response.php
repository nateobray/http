<?php

namespace obray\http;

class Response extends \obray\http\Transport
{
    static public function respond(int $status,string $contentType, string $body='')
    {
        $response = new \obray\http\Transport();
        $response->setStatus(new \obray\http\types\Status($status));
        $response->setHeaders(new \obray\http\Headers([
            "Content-Length" => mb_strlen($body, '8bit'),
            "Content-Type" => $contentType,
            "Connection" => "Keep-Alive"
        ]));
        $response->setBody(\obray\http\Body::decode($body));
        return $response;
    }
}