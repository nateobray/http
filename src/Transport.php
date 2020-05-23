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

    public static function decode(string $data)
    {
        $static = !(isset($this) && get_class($this) == __CLASS__);

        if($static) {
            $data = explode("\r\n\r\n", $data);
            $firstLine = strtok($data[0], "\r\n");
            $headers = str_replace($firstLine . "\r\n", "", $data[0]);
            $firstLine = explode(" ", $firstLine);
            
            // create transport object
            $transport = new \obray\http\Transport($firstLine[0], $firstLine[1], $firstLine[2]);

            // parse headers
            $transport->setHeaders(\obray\http\Headers::decode($headers));

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
        $encodedString .= "\r\n";
        if(!empty($this->body)){
            $encodedString .= $this->body->encode($this->getTransferEncoding());
        }
        return $encodedString;
    }

}