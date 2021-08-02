<?php

namespace obray\http;

class Transport
{
    private $method;
    private $uri;
    private $version;
    private $status;
    private $parameters = [];

    private $headers;
    private $body;
    private $bodyFormat = '';
    private $bodyBoundary = '';

    private $isComplete = false;

    private $cookies;
    private $sessionIds = [];

    private $startTime = 0;
    private $endTime = 0;
    
    public function __construct(string $method='', string $uri='', string $version='HTTP/1.1', \obray\http\Headers $headers=null)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->version = $version;
        $this->headers = $headers;
        $this->startTime();
    }

    public function startTime()
    {
        $this->startTime = microtime(true);
    }

    public function getDuration()
    {
        return (microtime(true) - $this->startTime) * 1000;
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

    public function getHeaders($key=null, $key2=null)
    {
        if($key !== null){
            return $this->headers->getHeader($key, $key2);
        }
        return $this->headers;
    }

    public function addHeader(\obray\http\Header $header): void
    {
        if(empty($this->headers)) $this->headers = new \obray\http\Headers([]);
        $this->headers->addHeader($header);
        // process meaningful headers
        $className = '\\obray\\http\\headers\\'.$header->getClassName();
        if(class_exists($className)){
            $className::decode($header, $this);
        }
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameters(string $key=null)
    {
        if($key !== null){
            try {
                if(!empty($this->parameters[$key])) return $this->parameters[$key];
                throw new \Exception("Parameter " . $key . " not found,");
            } catch (\Exception $e){
                print_r($e->getMessage() . "\n");
                return false;
            }
        }
        return $this->parameters;
    }

    public function getForm(string $key=null)
    {
        return $this->body->getForm($key);
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

    public function setBodyFormat(string $format, string $boundary='')
    {
        $this->bodyFormat = $format;
        $this->bodyBoundary = $boundary;
    }

    public function getBody(): \obray\http\Body
    {
        return $this->body;
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

    public function getStatusCode()
    {
        return intVal($this->status->encode());
    }

    public static function decodeProtocol(string $data): \obray\http\Transport
    {
        
        $data = explode(" ", $data);
        if(count($data) < 2) throw new \obray\exceptions\BadRequest400();
        if(!in_array($data[0],['HTTP/1.0', 'HTTP/1.1', 'HTTP/2.0'])) throw new \obray\exceptions\BadRequest400();
        try{
            $status = new \obray\http\types\Status(intVal($data[1]));
        } catch(\Excetpoin $e){
            $status = new \obray\http\types\Status(intVal($data[1]));
        }
        
        $transport = new \obray\http\Transport('', '', $data[0]);
        $transport->setStatus($status);
        return $transport;    
    }

    public static function decodeProtocolRequest(string $data): \obray\http\Transport
    {
        $data = explode(" ", $data);
        
        if(count($data) < 2) throw new \obray\exceptions\BadRequest400();
        if(!in_array($data[0], \obray\http\Methods::toArray())) throw new \obray\exceptions\BadRequest400();
        if(!in_array($data[count($data)-1], \obray\http\ProtocolVersions::toArray())) throw new \obray\exceptions\BadRequest400();

        $transport = new \obray\http\Transport( trim($data[0]), (count($data)>2)?$data[1]:'', $data[count($data)-1]);
        if((count($data)>2)){
            parse_str(parse_url($data[1], \PHP_URL_QUERY), $query);
            $transport->setParameters($query);
        }
        return $transport;
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

            // set parameters
            parse_str(parse_url($firstLine[1], \PHP_URL_QUERY), $query);
            $transport->setParameters($query);

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
                //$transport->complete();
            }

            // parse body
            if(!empty($data[1])){
                $length = strlen($data[1]);
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
