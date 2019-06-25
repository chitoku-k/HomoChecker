<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use Illuminate\Support\Facades\Cache;

/**
 * @method string loadIconMastodon(string $screen_name)
 * @method string loadIconTwitter(string $screen_name)
 * @method void saveIconMastodon(string $screen_name, string $url, int $expire)
 * @method void saveIconTwitter(string $screen_name, string $url, int $expire)
 */
class CacheService implements CacheServiceContract
{
    public function __call(string $name, array $arguments = [])
    {
        if (!preg_match('/(load|save)(.+)/', $name, $matches)) {
            throw new \LogicException('Method must start with load or save.');
        }

        /**
         * @var string $feature    "load" or "save"
         * @var string $identifier "iconMastodon" or "iconTwitter"
         */
        [ , $feature, $identifier ] = $matches;

        return $this->$feature(
            implode(
                ':',
                array_merge(
                    array_map(
                        'strtolower',
                        array_slice(preg_split('/(?=[A-Z])/', $identifier), 1),
                    ),
                    array_splice($arguments, 0, 1),
                ),
            ),
            $arguments,
        );
    }

    public function load(string $key, array $arguments = [])
    {
        return Cache::get($key, ...$arguments);
    }

    public function save(string $key, array $arguments = [])
    {
        Cache::put($key, ...$arguments);
    }
}
