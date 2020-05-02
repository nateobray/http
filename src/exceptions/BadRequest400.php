<?php
namespace obray\http\exceptions;

class BadRequest400 extends \Exception
{
    public function __construct($message="Bad Request", $code=400, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}