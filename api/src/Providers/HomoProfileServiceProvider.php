<?php
declare(strict_types=1);

namespace HomoChecker\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use HomoChecker\Http\RequestSigner;
use HomoChecker\Service\Profile\MastodonProfileService;
use HomoChecker\Service\Profile\XProfileService;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Prometheus\Counter;

final class HomoProfileServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->app->singleton('mastodon.client', function (Container $app) {
            $handler = HandlerStack::create();
            $handler->push($app->make('mastodon.signer'));

            $config = $app->make('config')->get('mastodon.client');
            $config['handler'] = $handler;

            return new Client($config);
        });

        $this->app->singleton('x.client', fn (Container $app) => new Client($app->make('config')->get('x.client')));

        $this->app->singleton('mastodon.signer', RequestSigner::class);
        $this->app->when(RequestSigner::class)
            ->needs('$id')
            ->give(fn (Container $app) => $app->make('config')->get('activityPub.actor')['id']);
        $this->app->when(RequestSigner::class)
            ->needs('$privateKeyPem')
            ->give(function (Container $app) {
                $actor = $app->make('config')->get('activityPub.actor');
                return \file_get_contents($actor['private_key']);
            });

        $this->app->when(MastodonProfileService::class)
            ->needs(ClientInterface::class)
            ->give('mastodon.client');

        $this->app->when(XProfileService::class)
            ->needs(ClientInterface::class)
            ->give('x.client');

        $this->app->when([MastodonProfileService::class, XProfileService::class])
            ->needs(Counter::class)
            ->give('collector.profile_error_total');

        $this->app->singleton('profiles', fn (Container $app) => collect([
            'mastodon' => $app->make(MastodonProfileService::class),
            'twitter' => $app->make(XProfileService::class),
        ]));
    }

    #[\Override]
    public function provides()
    {
        return [
            'profiles',
            'mastodon.client',
            'mastodon.signer',
            'x.client',
        ];
    }
}
