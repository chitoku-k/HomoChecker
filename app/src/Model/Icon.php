<?php
namespace HomoChecker\Model;

use mpyw\Co\Co;

class Icon
{
    public $screen_name;
    public $url;

    public function __construct($screen_name)
    {
        $this->screen_name = $screen_name;
    }

    protected function fetch(): \Generator
    {
        $ch = curl_init("https://twitter.com/intent/user?screen_name={$this->screen_name}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
        ]);
        if (preg_match('/src=(?:\"|\')(https:\/\/[ap]bs\.twimg\.com\/[^\"\']+)/', yield $ch, $matches)) {
            list(, $this->url) = $matches;
        }

        return $this;
    }

    public static function get($screen_name): \Generator
    {
        $self = new static($screen_name);
        return $self->fetch();
    }
}
