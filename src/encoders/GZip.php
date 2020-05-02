<?php

namespace obray\http\encoders;

class GZip
{
    public function encode(string $data): string
    {
        return $data;
    }

    public function decode(string $data): string
    {
        return $data;
    }
}