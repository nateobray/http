<?php

namespace obray\http\types;

class Pair implements \obray\http\interfaces\TypeInterface
{
    private $key;
    private $value;

    public function __construct(string $key, string $value)
    {
        $this->key = trim($key);
        $this->value = trim($value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function hasKey(string $key2)
    {
        if($this->key === $key2) return true;
        return false;
    }

    public function contains(string $value): bool
    {
        if($this->value == $value){
            return true;
        }
        return false;
    }

    public static function decode(string $value)
    {
        $pair = explode('=', $value);
        if(count($pair) <= 1) return false;
        return new \obray\http\types\Pair($pair[0], $pair[1]);
    }

    public function encode(): string
    {
        return $this->__toString();
    }

    public function __toString(){
        return $this->key . '=' . $this->value;
    }
}