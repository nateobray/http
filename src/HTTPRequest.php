<?php

namespace obray;

Class HTTPRequest
{
    
    protected $method;
    protected $host;
    protected $path;
    protected $query;
    protected $version;
    protected $headers;
    protected $data = '';
    protected $files = [];

    public function __construct($method, $scheme, $host, $port, $path, $query=NULL, $version='1.1')
    {
        $this->method = $method;
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = !empty($query)?('?'.$query):'';
        $this->version = $version;
        $this->headers = new \obray\HTTPHeaders();
    }

    public function setPostData($data)
    {
        //$this->headers["Transfer-Encoding"] = 'chunked';
        try{
            $contentType = $this->headers->get("Content-Type");
        } catch ( \Exception $e){
            $this->validatePostData($data);
        }
        
        if(empty($this->headers->get("Content-Type"))){
            throw new \Exception("Unable to determine Content-Type, please specify in your header data.", 500);
        }
        
        if(strpos($this->headers->get('Content-Type'),"multipart/form-data")!==false){
            $this->prepareMultipartFormData($data);
            return true;
        }

        if(strpos($this->headers->get('Content-Type'),"application/x-www-form-urlencoded")!==false){
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
            $this->headers->set(array("Content-Length" => strlen($data)));
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
        $this->headers->set("Content-Length", strlen($this->data));
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

        $this->data = "";
        forEach($data as $name => $value){
            if(in_array(gettype($value),['object','array'])) {
                $this->expandArrayOrObject($name, $value);
                continue;
            }
            $this->data .= "--".$this->boundary."\r\n";
            $this->data .= 'Content-Disposition: form-data; name="'.$name.'"'."\r\n";
            $this->data .= "\r\n";
            $this->data .= $value . "\r\n";
        }

        $this->data .= "\r\n--".$this->boundary."--\r\n";
        $this->headers->set("Content-Length", strlen($this->data));
        return true;
    }

    private function expandArrayOrObject($name, $data)
    {
        $boundary = md5(rand());
        
        forEach($data as $key => $value){
            if(in_array(gettype($value),['object','array'])) {
                $this->expandArrayOrObject($name.'['.$key.']', $value);
                continue;
            }
            $this->data .= "\r\n--".$this->boundary."\r\n";
            $this->data .= 'Content-Disposition: form-data; name="'.$name.'['.$key.']"'."\r\n";
            $this->data .= "\r\n";
            $this->data .= $value;
        }
    }

    public function validatePostData($data)
    {
        if(in_array(gettype($data),['string','double','integer','boolean'])){
            $this->headers->set(array("Content-Type" => "text/plain"));
        }
        if(in_array(gettype($data),['array','object'])){
            $this->boundary = md5(rand());
            $this->headers->set(array("Content-Type" => "multipart/form-data; boundary=".$this->boundary.""));
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
        if(empty($this->headers->get())) return $request . "\r\n";
        
        // write headers to request
        $request .= $this->headers;

        // return request
        return $request . "\r\n" . $this->data;
    }

    public function chunkEncode($data)
    {
        $length = strlen($data);
        $hex = dechex($length);
        $data = $hex . "\r\n" . $data . "\r\n0\r\n";
        return $data;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getScheme()
    {
        return $this->scheme;
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
        if(is_array($headers)) $this->headers->set($headers);
    }

}