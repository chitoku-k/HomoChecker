<?php
namespace HomoChecker\Model;

class Homo {
    public $screen_name;
    public $url;

    private static $sites = [
        'java_shit' => [
            'https://homo.chitoku.jp',
            'http://xn--ydko.chitoku.jp',
        ],
        // 'tuyapin' => 'http://homo.tuyapin.net',
        'hnle0' => [
            'https://homo.hinaloe.net',
            'http://homo.hnle.tk',
            'http://xn--ydko.hinaloe.net',
            'http://xn--ydko.hnle.tk',
        ],
        'azyobuzin' => [
            'http://homo.azyobuzi.net',
            'http://xn--ydko.azyobuzi.net',
        ],
        'u3g3' => [
            'https://homo.gomasy.jp',
            // 'https://homo.goma.ga',
        ],
        'G2U' => 'http://homo.mohyo.net',
        'Hexium310' => [
            'https://hexium310.github.io/homo',
            'http://homo.hexium.xyz',
        ],
        // 'DYGV' => [
        //    'http://homo.dygv.org',
        //    'http://xn--ydko.dygv.org',
        //    'http://xn--79jo.dygv.org',
        // ],
        'shibafu528' => 'http://homo.shibafu528.info',
        'kb10uy' => [
            'http://homo.kb10uy.org',
            'http://xn--ydko.kb10uy.org',
        ],
        'kazuki_kaihatu' => 'http://homo.kazukioishi.net',
        'tk1024_bot' => 'http://homo.tk1024.net',
        // 'nkpoid' => 'http://homo.nkpoid.pw',
        'Petitsurume' => 'https://petitsurume.github.io/homo/',
        // 'sudosan' => [
        //    'https://homo.sudosan.net',
        //    'https://homo.114514.jp',
        //    'http://homo.syaro.me',
        //    'http://homo.megune.com',
        //    'http://homo.chino.pw',
        //    'http://homo.chiya.pw',
        //    'http://homo.kokoa.pw',
        //    'http://homo.rize.pw',
        // ],
        '491MHz' => 'http://homo.491mhz.pw',
        'printf_moriken' => 'http://moriken.kimamass.com/homo',
        'homomaid' => 'http://homo.homomaid.com',
        'LaLN_' => [
            'https://homo.lunasys.tk',
            'https://homo.synchthia.net',
        ],
        // 'junT58' => 'http://homo.junt58.tk',
    ];

    public static function getAll() {
        return self::create(self::$sites);
    }

    public static function getByScreenName(string $screen_name) {
        return self::create([self::$sites[$screen_name] ?? null]);
    }

    public static function create(array $homo) {
        $result = [];
        foreach ($homo as $screen_name => $urls) {
            foreach ((array)$urls as $url) {
                $result[] = new self($screen_name, $url);
            }
        }
        return $result;
    }

    public function __construct($screen_name, $url) {
        $this->screen_name = $screen_name;
        $this->url = $url;
    }
}
