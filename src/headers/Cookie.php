<?php

namespace obray\http\headers;

class Cookie
{
    public static function decode(\obray\http\Header $header, \obray\http\Transport &$transport): void
    {
        $cookie = $header->getValue();
        $cookie = explode(';', $cookie);

        if(empty($cookie)) return;
        forEach($cookie as $index => $c){
            try {
                $cookie = \obray\http\Cookie::decode($c);
            } catch (\Exception $e) {
                continue;
            }
            $transport->setCookie($cookie);
        }
    }
}