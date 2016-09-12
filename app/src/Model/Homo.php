<?php
namespace HomoChecker\Model;

class Homo
{
    public $screen_name;
    public $url;

    private static $sites = [
        '4mcn' => [
            'http://homo.mizua.me',
            'http://xn--ydko.mizua.me',
        ],
        'AtiS' => [
            'https://homo.atis.ml',
            'http://xn--ydko.atis.ml',
        ],
        'azyobuzin' => [
            'http://homo.azyobuzi.net',
            'http://xn--ydko.azyobuzi.net',
        ],
        'CHIKEN_MAN_' => 'http://homo.xn--w8jwb2eudb.com',
        'DYGV' => 'http://homo.dygv.info',
        'G2U' => 'http://homo.mohyo.net',
        'Hexium310' => [
            'https://hexium310.github.io/homo',
            'http://homo.hexium.xyz',
        ],
        'hnle0' => [
            'https://homo.hinaloe.net',
            'http://homo.hnle.tk',
            'http://xn--ydko.hinaloe.net',
            'http://xn--ydko.hnle.tk',
        ],
        'homomaid' => 'http://homo.homomaid.com',
        'java_shit' => [
            'https://homo.chitoku.jp',
            'http://xn--ydko.chitoku.jp',
        ],
        'kb10uy' => [
            'http://homo.kb10uy.org',
            'http://xn--ydko.kb10uy.org',
        ],
        'LaLN_' => 'https://homo.synchthia.net',
        'mfqn' => 'http://homo.ni-vg.com',
        'myskng' => 'http://homo.kazukioishi.net',
        'owl_8' => 'http://homo.owl8.net',
        'pakutoma' => 'https://homo.pakutoma.pw',
        'paralleltree' => 'http://homo.paltee.net',
        'Petitsurume' => 'https://homo.surume.tk',
        'printf_moriken' => 'http://moriken.kimamass.com/homo',
        'Syenox' => [
            'http://homo.koutanakayama.org',
            'http://homo.nlinx.ne.jp',
            'http://homo.ncraft.top',
        ],
        'shibafu528' => 'http://homo.shibafu528.info',
        'tk1024_bot' => 'http://homo.tk1024.net',
        'u3g3' => [
            'http://homo.gomasy.jp',
            'https://homo.gomasy.jp',
        ],
    ];

    public static function getAll(): \Generator
    {
        return self::create(self::$sites);
    }

    public static function getByScreenName(string $screen_name): \Generator
    {
        foreach (self::$sites as $key => $site) {
            if (!strcasecmp($key, $screen_name)) {
                return self::create([
                    $key => $site,
                ]);
            }
        }
    }

    public static function create(array $homo): \Generator
    {
        foreach ($homo as $screen_name => $urls) {
            foreach ((array)$urls as $url) {
                yield new self($screen_name, $url);
            }
        }
    }

    public function __construct(string $screen_name, string $url)
    {
        $this->screen_name = $screen_name;
        $this->url = $url;
    }
}
