<?php

namespace obray\http;

class Header
{
    private $token;
    private $value;

    public function __construct(string $token, $value)
    {
        $this->token = $token;
        $this->value = $value;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function decode($header)
    {
        // normalize whitespace
        $header = str_replace(["  ", "\t ", " \t", "\t"], " ", $header);
        if(strpos($header, ':')===false) throw new \obray\http\exceptions\BadRequest400();

        $token = strtok($header, ":");
        $value = str_replace($token.':', '', $header);

        switch(strtolower($token)){
            // general headers
            case 'upgrade':
            case 'connection':
            case 'host':
            case 'origin':
            case 'pragma':
            case 'cache-control':
            case 'trailer':
                return new \obray\http\Header($token, \obray\http\types\Text::decode($value));
            case 'transfer-encoding':
                return new \obray\http\Header($token, \obray\http\types\HeaderList::decode($value, ',', '\obray\http\types\TransferCoding'));
            case 'via':
            case 'warning':
            // request headers
            case 'accept':
            case 'accept-charset':
            case 'accept-encoding':
            case 'accept-language':
            case 'expect':
            case 'from':
            case 'host':
            case 'if-match':
            case 'if-modified-since':
            case 'if-none-match':
            case 'if-range':
            case 'if-unmodified-since':
            case 'max-forwards':
            case 'proxy-authorization':
            case 'range':
            case 'referer':
            case 'te':
            case 'user-agent':
            // entity headers
            case 'content-encoding':
            case 'content-language':
            case 'content-length':
            case 'content-location':
            case 'content-md5':
            case 'content-range':
            case 'content-type':
            case 'expires':
            case 'last-modifiers':
            case 'extension-header':
                return new \obray\http\Header($token, \obray\http\types\Text::decode($value));
            default:
                return new \obray\http\Header($token, \obray\http\types\Text::decode($value));
            
        }

    }

    public function encode()
    {
        return $this->token . ': ' . $this->value->encode() . "\r\n";
    }
}