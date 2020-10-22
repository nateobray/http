<?php

namespace obray\http;

class Response extends \obray\http\Transport
{
    public function getURI()
    {
        return $this->uri;
    }
}