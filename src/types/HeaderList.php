<?php

namespace obray\http\types;

class HeaderList implements \obray\http\interfaces\TypeInterface
{
    private $values;
    private $properties;
    private $delimeter = ',';

    public function __construct(array $values, $delimeter=',')
    {
        $this->values = $values;
    }

    public function contains(string $value): bool
    {
        forEach($this->values as $v){
            if((string)$v == $value) return true;
        }
        return false;
    }

    public static function decode($data, $delimeter, $type)
    {
        $values = explode($delimeter, $data);
        if(!is_array($values)) $values = array(0 => $values);
        $newValues = [];
        forEach($values as $value) {
            $pair = \obray\http\types\Pair::decode($value);
            if($pair === false ) {
                $newValues[] = $type::decode($value);
            } else {
                $newValues[] = $pair;
            }
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

    public function getPairValue(string $key2)
    {
        forEach($this->values as $value){
            if(\get_class($value) === 'obray\http\types\Pair' && $value->hasKey($key2)){
                return $value->getValue();
            }
        }
        throw new \Exception("Could not find " . $key2 . "\n");
    }
}