<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use HomoChecker\Contracts\Service\CacheService as CacheServiceContract;
use HomoChecker\Contracts\Service\Client\Altsvc;
use HomoChecker\Contracts\Service\Client\Response;
use HomoChecker\Contracts\Service\ClientService as ClientServiceContract;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ClientService implements ClientServiceContract
{
    public function __construct(protected ClientInterface $client, protected CacheServiceContract $cache, protected int $redirect)
    {
    }

    /**
     * Get the responses for URL.
     * @param  string                                 $url The URL.
     * @return \Generator<PromiseInterface<Response>> The responses.
     */
    public function getAsync(string $url): \Generator
    {
        for ($i = 0; $i < $this->redirect; ++$i) {
            $options = [];

            if (str_starts_with($this->cache->loadAltsvc($url, ''), 'h3')) {
                $options['curl'] = [
                    CURLOPT_CERTINFO => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_3,
                ];
                $options['headers']['Alt-Used'] = parse_url($url, PHP_URL_HOST);
                $url = str($url)->replaceFirst('http://', 'https://')->toString();
            }

            yield $url => $this->client->requestAsync('GET', $url, $options + [
                RequestOptions::ON_HEADERS => function (ResponseInterface|\Throwable $response) use ($url) {
                    if ($response instanceof \Throwable) {
                        return;
                    }

                    $altsvc = $response->getHeaderLine('Alt-Svc');
                    if (!$altsvc) {
                        return;
                    }

                    /** @var ?Altsvc $h3 */
                    $h3 = str($altsvc)
                        ->split('/\s*,\s*/')
                        ->mapInto(Altsvc::class)
                        ->first(fn (Altsvc $item) => str_starts_with($item->getProtocolId(), 'h3'));
                    if (!$h3) {
                        return;
                    }

                    $this->cache->saveAltsvc($url, $h3->getProtocolId(), $h3->getMaxAge());
                },
                RequestOptions::ON_STATS => function (TransferStats $stats) use (&$result) {
                    $response = $stats->getResponse();
                    if (!$response) {
                        return;
                    }
                    $result = new Response($response);
                    $result->setTotalTime($stats->getTransferTime() ?? 0.0);
                    $result->setStartTransferTime($stats->getHandlerStat('starttransfer_time') ?? 0.0);
                    $result->setCertificates($stats->getHandlerStat('certinfo') ?? []);
                    $result->setHttpVersion($stats->getHandlerStat('http_version'));
                    $result->setPrimaryIP($stats->getHandlerStat('primary_ip'));
                },
            ])->then(function (Psr7Response $response) use (&$result, &$url) {
                $url = $response->getHeaderLine('Location');
                return $result;
            });

            if (!$url) {
                return;
            }
        }
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return $this->client->send($request, $options);
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return $this->client->sendAsync($request, $options);
    }

    public function request(string $method, $uri, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        return $this->client->requestAsync($method, $uri, $options);
    }

    public function getConfig(?string $option = null)
    {
        return $this->client->getConfig();
    }
}
