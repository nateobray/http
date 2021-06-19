<?php

namespace obray\http;

class Headers implements \Iterator
{
    private $position = 0;
    private $headers = [];
    private $map = [];

    // general headers
    private $transferEncodings = [];

    /**
     * Constructor
     * 
     * Build out all available headers
     */

    public function __construct(array $headers=[])
    {
        $this->position = 0;
        forEach($headers as $key => $value){
            $this->addHeader(new \obray\http\Header($key, $value));
        }
    }

    public function addHeader(\obray\http\Header $header)
    {
        $token = $header->getToken();
        $this->map[] = $token;
        $this->headers[$token] = $header;
        switch($token){
            case 'Transfer-Encoding':
                if(empty($this->transferEncodings)){
                    $this->transferEncodings = $header->getValue()->__toArray();
                    return;
                }
                $this->transferEncodings = array_merge($this->transferEncodings, $header->getValue()->__toArray());
            break;
        }
    }

    public function getTransferEncoding()
    {
        return $this->transferEncodings;
    }

    public function getHeader(string $key)
    {
        if(!isSet($this->headers[$key]) || $this->headers[$key] === null) throw new \Exception("Header " . $key . " not found.");
        return $this->headers[$key];
    }

    public function hasHeader(string $key, string $value=null): bool
    {
        if(!isSet($this->headers[$key]) || $this->headers[$key] === null) return false;
        if($value !== null && strcasecmp($this->headers[$key], $value) !== 0) return false;
        return true;
    }

    public static function decode(string $data)
    {
        $headers = new \obray\http\Headers();
        $data = explode("\r\n", $data);
        forEach($data as $index => $header){
            $headers->addHeader(\obray\http\Header::decode($header));
        }
        return $headers;
    }

    public function encode()
    {
        $encodedString = "";
        forEach($this->headers as $header){
            $encodedString .= $header->encode();
        }
        return $encodedString;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->headers[$this->map[$this->position]];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->map[$this->position]);
    }
}