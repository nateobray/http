<?php
namespace obray\http\types;

class Enum
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function decode($value)
    {
        if($value!==NULL){
            $value = trim($value);
            $callingClass = get_called_class();
            $reflectionClass = new \ReflectionClass(new $callingClass(""));
            $constants = $reflectionClass->getConstants();
            $key = array_search($value, $constants);
            if($key === false){
                throw new \obray\http\exceptions\BadRequest400();
            }
            return new \obray\http\types\TransferCoding($value);
        }
    }

    public function __toString()
    {
        return $this->encode();
    }

    public function encode()
    {
        return $this->value;
    }

}