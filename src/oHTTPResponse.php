<?php

namespace obray;

Class oHTTPResponse
{
    protected $rawHeader;
    protected $rawBody;

    protected $status;
    protected $statusDescription;
    protected $scheme;
    protected $version;

    protected $headers;

    public function setRawHeader($header)
    {
        $this->rawHeader = $header;
        $this->parseHeader($header);
    }

    public function setRawBody($body)
    {
        $this->rawBody = $body;
    }

    public function __toString()
    {
        return $this->rawHeader . "\r\n\r\n" . $this->rawBody;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->rawBody;
    }

    public function getTransferEncoding()
    {
        return !empty($this->headers['Transfer-Encoding'])?$this->headers['Transfer-Encoding']:'chunked';
    }

    private function parseHeader()
    {
        $headerLines = explode("\r\n",$this->rawHeader);
        $beginning = array_shift($headerLines);
        $this->parseBeginningLine($beginning);
        $this->parseHeaderLines($headerLines);
    }

    private function parseBeginningLine($beginningLine)
    {
        $lines = explode(" ",$beginningLine);
        $scheme = explode("/",$lines[0]);
        $this->scheme = $scheme[0];
        $this->version = $scheme[1];
        $this->status = $lines[1];
        $this->statusDescription = $lines[2];
    }

    private function parseHeaderLines($headerLines)
    {
        $headers = [];
        forEach($headerLines as $line){
            $line = explode(":",$line,2);
            $headers[$line[0]] = !empty($line[1])?trim($line[1]):NULL;
        }
        $this->headers = $headers;
    }

}