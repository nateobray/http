<?php

namespace obray\http;

class ProtocolVersions
{
    const HTTP1 = 'HTTP/1.0';
    const HTTP1_1 = 'HTTP/1.1';
    const HTTP2 = 'HTTP/2.0';
    
    public static function toArray()
    {
        return [
            SELF::HTTP1,
            SELF::HTTP1_1,
            SELF::HTTP2
        ];
    }
    
}