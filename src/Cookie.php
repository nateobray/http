<?php
namespace obray\http;

class Cookie 
{
    private $key;
    private $value;

    private $maxAge;
    private $expires;
    private $secure;
    private $httpOnly;
    private $sameSite;
    private $domain;
    private $path;

    private $isSetOnClient = false;

    public function __construct(string $key, string $value, array $properties=[])
    {
        $this->key = $key;
        $this->value = $value;
        if(!empty($properties['expires'])) $this->expires = strtotime($properties['expires']);
        if(!empty($properties['secure'])) $this->secure = (bool)$properties['secure'];
        if(!empty($properties['httpOnly'])) $this->httpOnly = (bool)$properties['httpOnly'];
        if(!empty($properties['sameSite'])) $this->sameSite = $this->validateSameSite(ucwords($properties['sameSite']));
        if(!empty($properties['domain'])) $this->domain = (string)$properties['domain'];
        if(!empty($properties['path'])) $this->path = (string)$properties['path'];
    }

    private function validateSameSite($value)
    {
        if(in_array($value, ['Lax', 'Strict', 'None'])) return $value;
        return 'Lax';
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function onClient(bool $isSet=null): bool
    {
        if($isSet !== null){
            $this->setOnClient = $isSet;
        }

        return $this->setOnClient;
    }

    public function __toString(): string
    {
        $string = $this->key . '=' . $this->value . ';';
        if($this->expires) $string .= ' Expires=' . gmdate('D, d M Y H:i:s T', $this->expires) . ';';
        if($this->secure) $string .= ' Secure;';
        if($this->httpOnly) $string .= ' HttpOnly;';
        if($this->sameSite) $string .= ' SameSite=' . $this->sameSite . ';';
        if(!empty($this->domain)) $string .= ' Domain=' . $this->domain . ';';
        if(!empty($this->path)) $string .= ' Path=' . $this->path . ';';
        return $string;
    }

    public function encode(): string
    {
        return $this->__toString();
    }
    
    static public function decode(string $c): \obray\http\Cookie
    {
        $c = explode('=',$c);
        if(count($c)!==2) throw new \Exception("Invalid cookie header received.");
        $cookie = new \obray\http\Cookie(trim($c[0]), trim($c[1]));
        $cookie->onClient(false);
        return new \obray\http\Cookie(trim($c[0]), trim($c[1]));
    }
}