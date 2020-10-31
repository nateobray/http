<?php

namespace obray\http\types;

class Text implements \obray\http\interfaces\TypeInterface
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = trim($value);
    }

    public function contains(string $value): bool
    {
        if($this->value == $value){ 
            return true;
        }
        return false;
    }

    public static function decode($value)
    {
        return new \obray\http\types\Text($value);
    }

    public function encode(): string
    {
        return $this->value;
    }

    public function __toString(){
        return $this->value;
    }
}