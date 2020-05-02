<?php

namespace obray\http\types;

class HeaderList
{
    private $values;
    private $delimeter = ',';

    public function __construct(array $values, $delimeter=',')
    {
        $this->values = $values;
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