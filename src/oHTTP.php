<?php

namespace obray;

Class oHTTP
{

    const GET = 'GET';
    const POST = 'POST';
    const HEAD = 'HEAD';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const CONNECT = 'CONNECT';
    const OPTIONS = 'OPTIONS';
    const TRACE = 'TRACE';
    
    protected $requests = [];
    protected $callbacks = [];
    protected $responses = [];

    public function addRequest($url, $method=GET, $data=NULL, $headers=[], $callback=NULL)
    {
        // parse url components
        $components = parse_url($url);

        // parse host
        if(empty($components["host"])){
            throw new \Exception("No host specified",500);
        }
        $host = $components["host"];

        // parse scheme
        if(empty($components['scheme']) || ($components['scheme'] !== 'https' && $components['scheme'] !== 'http')){
            throw new \Exception("Invalid scheme: only http or https is supported.",500);
        }
        $scheme = $components["scheme"];
        $port = $scheme==='http'?80:443;

        // path
        $path = !empty($components['path'])?$components['path']:'/';

        //query
        $query = !empty($components['query'])?$components['query']:NULL;

        // create request
        $request = new \obray\oHTTPRequest($method, $scheme, $host, $port, $path, $query, '1.1');
        $request->setHeaders($headers);
        if($data!==NULL){
            $request->setPostData($data);
        }
        $this->callbacks[] = $callback;
        $this->requests[] = $request;
    }

    public function setPostData($data)
    {
        if($data===NULL) return;
        $this->oHTTPRequest->setPostData($data);
    }

    public function send()
    {
        $responses = [];
        forEach($this->requests as $index => $request){
            // connect and send request
            $socket = stream_socket_client(($request->getScheme()==='http'?'tcp':'ssl') . "://" . $request->getHost() . ":" . $request->port, $errno, $errstr, 30);
            stream_set_blocking($socket, false);
            if (!$socket) throw new \Exception("$errstr ($errno)");
            if(fwrite($socket, $request)===false){
                throw new \Exception("Unable to send request.",500);
            }
        
            // create response object
            $oHTTPResponse = new \obray\oHTTPResponse();
            
            // read header and apply to response object
            $header = $this->readHeader($socket);
            $oHTTPResponse->setRawHeader($header);

            // read body and apply to response object
            $body = $this->readBody($socket,$oHTTPResponse->getTransferEncoding());
            $oHTTPResponse->setRawBody($body);

            // check if we have a valid callback
            if( is_callable($this->callbacks[$index]) ){
                ($this->callbacks[$index])($oHTTPResponse);
            }

            // store response
            $responses[] = $oHTTPResponse;

            // close connection
            fclose($socket);
        }
        // return response
        return $responses;
    }

    public function getRequest()
    {
        return $this->oHTTPRequest;
    }

    private function readHeader($socket)
    {
        return $this->fReadStream($socket,1,"\r\n\r\n");
    }

    private function readBody($socket,$encoding='chunked')
    {
        if($encoding!==NULL && $encoding !== 'chunked'){
            throw new \Exception("Transfer-Encoding not implemented.",501);
        }
        $end = "\r\n0\r\n";
        $chunkedData = $this->fReadStream($socket,1024*1000,$end);
        $decodedBody = $this->decodeChunkedData($chunkedData);
        return $decodedBody;
    }

    private function decodeChunkedData($data){
        for ($res = ''; !empty($data); $data = trim($data)) {
            $posOfLen = strpos($data, "\r\n");
            $lengthOfChunk = hexdec(substr($data, 0, $posOfLen));
            $res .= substr($data, $posOfLen + 2, (int)$lengthOfChunk);
            $data = substr($data, $posOfLen + 2 + (int)$lengthOfChunk);
        }
        return $res;
    }

    private function fReadStream($socket,$length,$end=NULL,$timeout=30)
    {    
        $response = ''; 
        $start = microtime(TRUE);
        $endSequence = $end;
        while( !feof($socket) ){
            $new = fread($socket, $length);
            $response .= $new;
            if($end !== NULL && $length === 1 && $new === $endSequence[0]){
                $endSequence = substr($endSequence,1,strlen($endSequence));
            } else if($length === 1) {
                $endSequence = $end;
            }
            if($length === 1 && $endSequence !== NULL && empty($endSequence)){
                return rtrim($response,$end);
            }
            if( strlen($new) === 0 && strlen($response) !== 0 ){ 
                return $response; 
            }
            $current = microtime(TRUE);
            if( $timeout <= $current-$start ){ 
                throw new \Exception("Reading stream timed out.");
            }
            if($length > 1 && strpos($response,$end) !== FALSE){
                return rtrim($response,$end);
            }
        }
        return $request;
    }

}