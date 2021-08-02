<?php

namespace obray\http;

class Response extends \obray\http\Transport
{
    static public function respond(int $status, array $headers=[], string $body='')
    {
        $response = new \obray\http\Transport();
        $response->setStatus(new \obray\http\types\Status($status));
        
        $response->setHeaders(new \obray\http\Headers(array_merge([
            "Content-Length" => strlen($body),
            "Connection" => "Keep-Alive"
        ], $headers)));
        $response->setBody(\obray\http\Body::decode($body));
        return $response;
    }
}