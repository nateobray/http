<?php

namespace obray;

Class oHTTPRequest
{
    
    protected $method;
    protected $host;
    protected $path;
    protected $query;
    protected $version;
    protected $headers = [];
    protected $data = '';
    protected $files = [];

    public function __construct($method, $host, $path, $query=NULL, $version='1.1')
    {
        $this->method = $method;
        $this->host = $host;
        $this->path = $path;
        $this->query = !empty($query)?('?'.$query):'';
        $this->version = $version;
    }

    public function setPostData($data)
    {
        if(empty($this->headers["Content-Type"]) ){
            $this->validatePostData($data);
        }

        if(empty($this->headers["Content-Type"]) ){
            throw new \Exception("Unable to determine Content-Type, please specify in your header data.", 500);
        }

        if(strpos($this->headers['Content-Type'],"multipart/form-data")!==false){
            $this->prepareMultipartFormData($data);
            return true;
        }

        if(strpos($this->headers['Content-Type'],"application/x-www-form-urlencoded")!==false){
            $this->prepareURLEncodedData($data);
            return true;
        }

        return $this->prepareStringData($data);
    }

    private function prepareStringData($data)
    {
        if(
            ( !is_array( $data ) ) &&
            ( ( !is_object( $data ) && settype( $data, 'string' ) !== false ) ||
            ( is_object( $data ) && method_exists( $data, '__toString' ) ) )
        )
        {
            $this->data = $data;
            return true;
        }

        throw new \Exception("Unable to convert post data to string.", 500);
        
    }

    private function prepareURLEncodedData($data)
    {
        $this->data = [];
        if(in_array(gettype($data),['string','double','integer','boolean'])){
            $this->data["data"] = $data;
        }
        $this->data = http_build_query($this->data);
        return true;
    }

    private function prepareMultipartFormData($data)
    {
        if(in_array(gettype($data),['string','double','integer','boolean'])){
            $data["data"] = $data;
        }
        if(in_array(gettype($data),['object'])){
            $data = (array)$data;
        }
        $this->data = '';
        forEach($data as $name => $value){
            $this->data .= "--boundary\r\n";
            $this->data .= 'Content-Disposition: form-data; name="'.$name.'"'."\r\n";
            $this->data .= "\r\n";
            $this->data .= $value . "\r\n";
        }
        return true;
    }

    public function validatePostData($data)
    {
        if(in_array(gettype($data),['string','double','integer','boolean'])){
            $this->headers["Content-Type"] = 'text/plain';
        }
        if(in_array(gettype($data),['array','object'])){
            $this->headers["Content-Type"] = 'multipart/form-data;boundary="boundary"';
        }
        if(in_array(gettype($data),['resource','resource (closed)','NULL','unknown type'])){
            throw new \Exception("Unable to post this type of data");
        }
    }

    public function __toString()
    {
        // write beginning line
        $request  = $this->method. ' ' . $this->path . $this->query . ' HTTP/' . $this->version . "\r\n";
        $request .= "Host: " . $this->host . "\r\n";
        
        // if no headers return request
        if(empty($this->headers)) return $request . "\r\n";
        
        // write headers to request
        forEach($this->headers as $key => $value){
            $request .= $key . ": " . $value . "\r\n";
        }

        // return request
        return $request . "\r\n" . $this->data;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

}