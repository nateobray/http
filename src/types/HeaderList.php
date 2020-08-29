<?php

namespace obray\http\types;

class HeaderList implements \obray\http\interfaces\TypeInterface
{
    private $values;
    private $delimeter = ',';

    public function __construct(array $values, $delimeter=',')
    {
        $this->values = $values;
    }

    public function contains(string $value): bool
    {
        forEach($values as $v){
            if($v->getValue() == $value) return true;
        }
        return false;
    }

    public function decode($data, $delimeter, $type)
    {
        $values = explode($delimeter, $data);
        $newValues = [];
        forEach($values as $value){
            $newValues[] = $type::decode($value);
        }
        return new \obray\http\types\HeaderList($newValues, $delimeter);
    }

    public function encode()
    {
        $encodedString = '';
        forEach($this->values as $value){
            if(!empty($encodedString)) $encodedString .= ',';
            $encodedString .= $value;
        }
        return $encodedString;
    }

    public function __toArray()
    {
        return $this->values;
    }
}