<?php
namespace obray\http\types;

class MIME implements \obray\http\interfaces\TypeInterface
{
    const TEXT = 'text/plain';
    const HTML = 'text/html';
    const JAVASCRIPT = 'text/javascript';
    const CSS = 'text/css';
    const JSON = 'application/json';
    const ICO = 'image/vnd.microsoft.icon';
    const GZIP = 'application/gzip';
    const JPEG = 'image/jpeg';
    const PNG = 'image/png';
    const GIF = 'image/gif';
    const SVG = 'image/svg+xml';

    const EXTENSIONS = [
        '/' => self::HTML,
        'txt' => self::TEXT,
        'html' => self::HTML,
        'htm' => self::HTML,
        'js' => self::JAVASCRIPT,
        'css' => self::CSS,
        'json' => self::JSON,
        'ico' => self::ICO,
        'gz' => self::GZIP,
        'jpg' => self::JPEG,
        'jpeg' => self::JPEG,
        'png' => self::JPEG,
        'gif' => self::GIF,
        'svg' => self::SVG
    ];

    public function __construct(string $mime=null)
    {
        $this->mime = $mime;
    }

    public function contains(string $value): bool
    {
        return $this->mime == $value;
    }

    static public function getSetMimeFromExtension(string $ext): \obray\http\types\MIME
    {
        
        $ext = explode('.', $ext);
        $ext = $ext[count($ext)-1];
        print_r(\obray\http\Types\MIME::EXTENSIONS[$ext]);
        if(empty(\obray\http\Types\MIME::EXTENSIONS[$ext])){
            throw new \Exception("Invalid MIME type");
        }
        return new \obray\http\types\MIME(\obray\http\Types\MIME::EXTENSIONS[$ext]);
    }

    public function __toString(): string
    {
        return (string)$this->mime;
    }
}