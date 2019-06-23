<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use HomoChecker\Service\Profile\MastodonProfileService;
use HomoChecker\Service\Profile\TwitterProfileService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class HomoProfileServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('profiles', function (Container $app) {
            return collect([
                'mastodon' => $app->make(MastodonProfileService::class),
                'twitter' => $app->make(TwitterProfileService::class),
            ]);
        });
    }

    public function provides()
    {
        return [
            'profiles',
        ];
    }
}
