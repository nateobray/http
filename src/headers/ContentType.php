<?php

namespace obray\http\headers;

class ContentType
{
    private $key;
    private $values;

    static public function decode(\obray\http\Header $header, \obray\http\Transport &$transport)
    {
        if(strpos((string)$header, 'multipart/form-data;') !== false){
            $parts = explode('; ', (string)$header);
            $pair = explode('=',$parts[1]);
            $transport->setBodyFormat('multipart/form-data', $pair[1]);
        }
    }

    public function encode()
    {

    }
}