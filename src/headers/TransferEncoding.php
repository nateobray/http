<?php

namespace obray\http\headers;

class TransferEncoding
{
    private $key;
    private $values;

    public function decode($value)
    {
        $values = explode(" ", $value);
        forEach($values as $value){
            
        }
    }

    public function encode()
    {

    }
}