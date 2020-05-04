<?php

namespace obray\http\types;

class Text
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = trim($value);
    }

    public static function decode($value)
    {
        return new \obray\http\types\Text($value);
    }

    public function encode(): string
    {
        return $this->value;
    }
}