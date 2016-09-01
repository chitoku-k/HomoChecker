<?php
namespace HomoChecker\Model;

use mpyw\Co\Co;
use mpyw\Co\CURLException;

class Icon
{
    public static $default = 'https://abs.twimg.com/sticky/default_profile_images/default_profile_0_200x200.png';

    public $screen_name;
    public $url;

    public function __construct(string $screen_name)
    {
        $this->screen_name = $screen_name;
    }

    protected function fetchAsync(): \Generator
    {
        $ch = curl_init("https://twitter.com/intent/user?screen_name={$this->screen_name}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR    => true,
        ]);

        try {
            if (preg_match('/src=(?:\"|\')(https:\/\/[ap]bs\.twimg\.com\/[^\"\']+)/', yield $ch, $matches)) {
                list(, $this->url) = $matches;
            }
        } catch (CURLException $e) {
            $this->url = static::$default;
        }

        return $this;
    }

    public static function getAsync(string $screen_name): \Generator
    {
        $self = new static($screen_name);
        return $self->fetchAsync();
    }
}
