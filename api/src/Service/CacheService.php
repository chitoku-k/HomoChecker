<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @method ?string loadIconMastodon(string $screen_name)
 * @method ?string loadIconTwitter(string $screen_name)
 * @method ?string loadAltsvc(string $url)
 * @method void    saveIconMastodon(string $screen_name, string $url, int $expire)
 * @method void    saveIconTwitter(string $screen_name, string $url, int $expire)
 * @method void    saveAltsvc(string $url, string $value, int $expire)
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
         * @var string $identifier "IconMastodon" or "IconTwitter"
         */
        [ , $feature, $identifier ] = $matches;

        return $this->{$feature}(
            collect(preg_split('/(?=[A-Z])/', Str::camel($identifier)))
                ->map(fn (string $item) => Str::lower($item))
                ->push(collect($arguments)->first())
                ->join(':'),
            collect($arguments)
                ->slice(1)
                ->toArray(),
        );
    }

    public function load(string $key, array $arguments = []): ?string
    {
        return Cache::get($key, ...$arguments);
    }

    public function save(string $key, array $arguments = []): void
    {
        Cache::put($key, ...$arguments);
    }
}
