<?php
namespace HomoChecker\Model;

class Homo
{
    public $screen_name;
    public $url;

    private static $sites = [
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
        'LaLN_' => [
            'https://homo.lunasys.tk',
            'https://homo.synchthia.net',
        ],
        'myskng' => 'http://homo.kazukioishi.net',
        'owl_8' => 'http://homo.owl8.net',
        'pakutoma' => 'https://homo.pakutoma.pw',
        'Petitsurume' => 'https://petitsurume.github.io/homo',
        'printf_moriken' => 'http://moriken.kimamass.com/homo',
        'Syenox' => [
            'http://homo.koutanakayama.org',
            'http://homo.nlinx.ne.jp',
            'http://homo.ncraft.top',
        ],
        'shibafu528' => 'http://homo.shibafu528.info',
        'snowhite0804' => 'https://homo.snowhite.tk',
        'tk1024_bot' => 'http://homo.tk1024.net',
        'u3g3' => 'https://homo.gomasy.jp',
    ];

    public static function getAll(): array
    {
        return self::create(self::$sites);
    }

    public static function getByScreenName(string $screen_name): array
    {
        return self::create([
            $screen_name => self::$sites[$screen_name] ?? null,
        ]);
    }

    public static function create(array $homo): array
    {
        $result = [];
        foreach ($homo as $screen_name => $urls) {
            foreach ((array)$urls as $url) {
                $result[] = new self($screen_name, $url);
            }
        }
        return $result;
    }

    public function __construct($screen_name, $url)
    {
        $this->screen_name = $screen_name;
        $this->url = $url;
    }
}
