<?php

namespace obray\http;

class Methods
{
    const OPTIONS = 'OPTIONS';
    const GET = 'GET';
    const HEAD = 'HEAD';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const TRACE = 'TRACE';
    const CONNECT = 'CONNECT';

    public static function toArray()
    {
        return [
            SELF::OPTIONS,
            SELF::GET,
            SELF::HEAD,
            SELF::POST,
            SELF::PUT,
            SELF::DELETE,
            SELF::TRACE,
            SELF::CONNECT
        ];
    }
    
}