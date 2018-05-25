<?php

namespace obray;

Class oHTTPHeaders
{
    protected $headers = [];

    /**
     * Parse
     * 
     * This takes a raw header string and parses into a headers array
     * 
     * @param string $headerString this is the raw header string from the HTTP request without the request line
     * 
     * @return array
     */
    public function parse(string $headersString): array
    {
        $headers = [];
        forEach($headerLines as $line){
            $line = explode(":",$line,2);
            $headers[$line[0]] = !empty($line[1])?trim($line[1]):NULL;
        }
        $this->headers = $headers;
        return $headers;
    }

    /**
     * add header
     * 
     * This adds a header key/value pair to the headers array
     * 
     * @param string $key this is the case-sensitive key for the header
     * @param string $value this is the value of the header
     * 
     * @return void
     */
    public function add(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    /**
     * Set Headers
     * 
     * This sets the value of headers to the passed array.
     * 
     * @param array $headers this needs to be an array of valid headers
     * 
     * @return void
     */
    public function set(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * get retreives the value of a specific header or the entire array
     * 
     * If a key is passed then it gives the vlue of that key in the header array or
     * if null returns the entire header array
     * 
     * @param string $key this is the case-sensitive key for the header
     * 
     * @return mixed
     */
    public function get(string $key=null)
    {
        // return entire header array
        if($key===null){
            return $this->headers;
        }
        // return value of specific header (case-sensitive)
        if(isSet($this->headers[$key])){
            return $this->headers[$key];
        }
        // throw an exception if specified header key does not exist
        throw new \Exception("This value does not exist in the headers array.",500);
    }

    /**
     * To String Magic Method
     * 
     * This takes the headers array, converts into a header string, and returns it
     * 
     * @return string
     */
    public function __toString(): string
    {
        $headerString = '';
        forEach($this->headers as $key => $value){
            $headerString .= $key . ': ' . $value;
        }
        return $headerString;
    }
    

}