<?php

namespace obray\http\encoders;

class Chunked
{
    private $data = "";
    private $isComplete = false;
    private $chunks = [];

    public function addChunk(int $length): void
    {
        $this->chunks[] = $length;
    }

    public function getChunks()
    {
        return $this->chunks;
    }

    public function complete(): void
    {
        $this->isComplete = true;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function isComplete()
    {
        return $this->isComplete;
    }

    public function getData(): string
    {
        return $this->data;
    }

    static public function encode(string $data, int $start=0, int $length): string
    {
        $dataToWrite = substr($data, $start, $length);
        $length = strlen($dataToWrite);
        $dataToWrite = dechex($length) . "\r\n" . $dataToWrite . "\r\n";
        return $dataToWrite;
    }

    public function decode(string $data): \obray\http\encoders\Chunked
    {
        $chunks = new \obray\http\encoders\Chunked();
        $length = -1; $content = ""; $offset = 0;
        while($length !== 0){
            if($offset > strlen($data)) return $content;
            $chunkHeader = substr($data, 0, strpos($data,"\r\n",$offset));
            $headerLength = strlen($chunkHeader . "\r\n");
            $length = intval(hexdec(strtok($chunkHeader, ";")));
            $offset = ($headerLength + $length + 2);
            $newContent = substr($data, $headerLength, $length);
            if(strlen($newContent) !== $length) throw new \obray\http\exceptions\BadRequest400();
            $content .= $newContent;
            $chunks->addChunk($length);
        }
        if($length !== 0) $chunks->complete();
        $chunks->setData($content);
        return $chunks;
    }
}