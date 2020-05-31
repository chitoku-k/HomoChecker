<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use HomoChecker\Service\Profile\MastodonProfileService;
use HomoChecker\Service\Profile\TwitterProfileService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class HomoProfileServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('twitter.client', function (Container $app) {
            $handler = HandlerStack::create();
            $handler->push($app->make('twitter.oauth'));

            $config = $app->make('config')->get('twitter.client');
            $config['handler'] = $handler;

            return new Client($config);
        });

        $this->app->singleton('twitter.oauth', Oauth1::class);
        $this->app->when(Oauth1::class)
            ->needs('$config')
            ->give(fn (Container $app) => $app->make('config')->get('twitter.oauth'));

        $this->app->when(TwitterProfileService::class)
            ->needs(ClientInterface::class)
            ->give('twitter.client');

        $this->app->singleton('profiles', fn (Container $app) => collect([
            'mastodon' => $app->make(MastodonProfileService::class),
            'twitter' => $app->make(TwitterProfileService::class),
        ]));
    }

    public function provides()
    {
        return [
            'profiles',
            'twitter.client',
            'twitter.oauth',
        ];
    }
}
