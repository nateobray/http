<?php

namespace obray\http;

class Transport
{
    private $method;
    private $uri;
    private $version;
    private $status;

    private $headers;
    private $body;

    private $isComplete = false;

    private $cookies;
    private $sessionIds = [];
    
    public function __construct(string $method='', string $uri='', string $version='HTTP/1.1', \obray\http\Headers $headers=null)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->version = $version;
    }

    public function getTransferEncoding()
    {
        if(!empty($this->headers)){
            return $this->headers->getTransferEncoding();
        }
        return ["identity"];
    }

    public function setHeaders($headers): void
    {
        $this->headers = $headers;
    }

    public function getHeaders($key=null)
    {
        if($key !== null){
            return $this->headers->getHeader($key);
        }
        return $this->headers;
    }

    public function getURI(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function complete()
    {
        $this->isComplete = true;
    }

    public function isComplete()
    {
        return $this->isComplete;
    }

    public function setStatus(\obray\http\types\Status $status)
    {
        $this->status = $status;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function getCookie($key)
    {
        if(empty($this->cookies->{$key})) return false;
        return $this->cookies->{$key};
    }

    public function addSessionCookies(array $cookies)
    {
        forEach($cookies as $cookie){
            $this->setCookie($cookie);
        }
    }

    public function setCookie(\obray\http\Cookie $cookie): void
    {
        if(empty($this->cookies)) $this->cookies = new \stdClass();
        $this->cookies->{trim($cookie->getKey())} = $cookie;
    }

    public function setSessions(array $sessionIds)
    {
        $this->sessionIds = $sessionIds;
    }

    public function getSessions()
    {
        return $this->sessionIds;
    }

    public function setSessionId($key, $id)
    {
        $this->sessionIds[$key] = $id;
    }

    public function refreshSession(string $key)
    {
        $this->sessions[$key]->refresh();
    }

    public static function decode(string $data)
    {
        if(empty($data)) return;
        $static = !(isset($this) && get_class($this) == __CLASS__);

        if($static) {
            $data = explode("\r\n\r\n", $data);
            $firstLine = strtok($data[0], "\r\n");
            $headers = str_replace($firstLine . "\r\n", "", $data[0]);
            $firstLine = explode(" ", $firstLine);
            
            if(count($firstLine) !== 3){
                throw new \Exception("Bad Request");
            }
            
            // create transport object
            $transport = new \obray\http\Transport($firstLine[0], $firstLine[1], $firstLine[2]);

            // parse headers
            $transport->setHeaders(\obray\http\Headers::decode($headers));

            // process meaningful headers
            $headers = $transport->getHeaders();
            forEach($headers as $index => $header){
                $className = '\obray\http\headers\\'.$header->getClassName();
                if(class_exists($className)){
                    $className::decode($header, $transport);
                }
            }

            // determine if we have the complete request
            $transferEncoding = $transport->getTransferEncoding();
            if($transferEncoding !== \obray\http\types\TransferCoding::CHUNKED){
                $transport->complete();
            }

            // parse body
            if(!empty($data[1])){
                $body = \obray\http\Body::decode($data[1], $transferEncoding);
                $transport->setBody($body);
                if($body->isComplete()) $transport->complete();
            }

            return $transport;
        }

        $this->handleAdditionalData($data);
        return;
    }

    private function handleAdditionalData(string $data)
    {
        $transferEncoding = $transport->getTransferEncoding();
        \obray\http\Body::decode($data, $transferEncoding, $this->body);
    }

    public function encode()
    {
        $encodedString = "";
        if(empty($this->status)){
            $encodedString = $this->method . ' ' . $this->uri . ' ' . $this->version . "\r\n";
        } else {
            $encodedString = $this->version . ' ' . $this->status->encode() . "\r\n";
        }
        if(!empty($this->headers)){
            $encodedString .= $this->headers->encode();
        }
        if(!empty($this->cookies)){
            forEach($this->cookies as $cookie){
                $encodedString .= 'Set-Cookie: ' . $cookie->encode() . "\r\n";
            }
        }
        $encodedString .= "\r\n";
        if(!empty($this->body)){
            $encodedString .= $this->body->encode($this->getTransferEncoding());
        }
        return $encodedString;
    }

}
