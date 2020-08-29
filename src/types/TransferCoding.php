<?php

namespace obray\http\types;

class TransferCoding extends \obray\http\types\Enum
{
    const CHUNKED = 'chunked';
    const IDENTITY = 'identity';
    const GZIP = 'gzip';
    const COMPRESS = 'compress';
    const DEFLATE = 'deflate';
}