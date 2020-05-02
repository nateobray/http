<?php
namespace obray\http\exceptions;

class Unimplemented501 extends \Exception
{
    public function __construct($message="Unimplemented", $code=501, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}