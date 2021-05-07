<?php

namespace obray\http;

class Body
{
    private $data = "";
    private $length = 0;

    private $isComplete = false;
    private $chunks = [];

    public function __construct(string $body)
    {
        $this->data = $body;
        $this->length = strlen($body);
    }

    public function mergeChunks(array $chunks)
    {
        $this->chunks = array_merge($this->chunks, $chunks);
    }

    public function complete()
    {
        $this->isComplete = true;
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    public function concat(string $data)
    {
        $this->data .= $data;
    }

    public static function decode(string $data, array $encodings=["identity"], \obray\http\Body &$body=null)
    {
        // handle chunked encoding (chunked must always be decoded first)
        if($index = array_search(\obray\http\types\TransferCoding::CHUNKED, $encodings) !== false){
            if($body !== null){
                $chunks = \obray\http\encoders\Chunked::decode($data);
                $body->concat($chunks->getData());
            } else {
                $chunks = \obray\http\encoders\Chunked::decode($data);
                $body = new \obray\http\Body($chunks->getData());
            }
            $body->mergeChunks($chunks->getChunks());
            if($chunks->isComplete() === true) $body->complete();
            if($body->isComplete() === false) return $body;
            unset($encodings[$index]);
        }

        // handle any additional encodings
        forEach($encodings as $encoding){
            switch($encoding){
                case \obray\http\types\TransferCoding::GZIP:
                    $body = new \obray\http\Body(\obray\http\encoders\GZip::decode($data));
                    break;
                case \obray\http\types\TransferCoding::COMPRESS:
                    $body = new \obray\http\Body(\obray\http\encoders\Compress::decode($data));
                    break;
                case \obray\http\types\TransferCoding::DEFLATE:
                    $body = new \obray\http\Body(\obray\http\encoders\Deflate::decode($data));
                    break;
                case \obray\http\types\TransferCoding::IDENTITY:
                    $body = new \obray\http\Body($data);
                    break;
                default:
                    throw new \obray\http\exceptions\Unimplemented501();
            }
        }

        // ensure we have a body to return
        if(empty($body)) $body = new \obray\http\Body($data);

        return $body;
    }

    public function encode($encodings=["identity"], string $data=null)
    {
        if(empty($data)) {
            $data = $this->data;
        }
        
        // encode body
        forEach($encodings as $encoding) {
            switch($encoding) {
                case "chunked":
                    // do nothing yet
                    break;
                case "gzip":
                    $data = \obray\http\encoders\GZip::encode($data);
                    break;
                case "compress":
                    $data = \obray\http\encoders\Compress::encode($data);
                    break;
                case "deflate":
                    $data = \obray\http\encoders\Deflate::encode($data);
                    break;
                case "identity":
                    // do nothing
                    break;
                default:
                    throw new \obray\http\exceptions\Unimplemented501();
            }
        }

        // handle chunked encoding, this should ALWAYS be done last
        
        if($index = array_search(\obray\http\types\TransferCoding::CHUNKED, $encodings) !== false){
            $chunkedData = ""; $offset = 0;
            forEach($this->chunks as $length){
                $chunkedData .= \obray\http\encoders\Chunked::encode($data, $offset, $length);
                $offset += $length;
            }
            // allow data to be passed here in chunks
            //$this->data .= $data;
            //$this->chunks[] = strlen($data);
            //return \obray\http\encoders\Chunked::encode($data, strlen($data));
            $data = $chunkedData;
        }
        return $data;
    }
}