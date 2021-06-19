<?php
namespace obray\http\formats;

class MultipartFormData
{
    public static function parse(string $data, string $boundary)
    {
        $formData = [];
        $parts = explode(trim($boundary), $data);
        forEach($parts as $part){
            $content = explode(';', $part);
            if(empty($content[1])) continue;
            $keyPair = explode("\r\n\r\n", $content[1]);
            if(empty($keyPair[1])) continue;
            $keyPair[0] = trim(explode('=', $keyPair[0])[1],'"');
            $keyPair[1] = trim($keyPair[1], "\r\n--");
            $formData[$keyPair[0]] = $keyPair[1];
        }
        return $formData;
    }
}