<?php
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    public function testTransport()
    {
        $dirs = scandir("test/requests");
        array_shift($dirs); array_shift($dirs);
        forEach($dirs as $d){
            $request = file_get_contents("test/requests/" . $d);
            $transport = \obray\http\Transport::decode($request);
            $encodedRequest = $transport->encode();
            $this->assertEquals($request, $encodedRequest);
        }
    }

    
}