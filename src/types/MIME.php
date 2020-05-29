<?php
namespace obray\http\types;

class MIME
{
    const TEXT = 'text/plain';
    const HTML = 'text/html';
    const JAVASCRIPT = 'text/javascript';
    const CSS = 'text/css';
    const JSON = 'application/json';
    const ICO = 'image/vnd.microsoft.icon';
    const GZIP = 'application/gzip';
    const JPEG = 'image/jpeg';
    const GIF = 'image/gif';

    private $extensions = [
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
        'gif' => self::GIF
    ];

    public function __construct(string $mime=null)
    {
        $this->mime = $mime;
    }

    public function getSetMimeFromExtension(string $ext): \obray\http\types\MIME
    {
        $ext = explode('.', $ext);
        $ext = $ext[count($ext)-1];
        if(!empty($this->extensions[$ext])){
            $this->mime = $this->extensions[$ext];
        }
        return $this;
    }

    public function __toString(): string
    {
        return (string)$this->mime;
    }
}