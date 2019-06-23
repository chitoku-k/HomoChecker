<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Exception\RequestException;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Contracts\Service\ProfileService as ProfileServiceContract;
use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Validator\ValidationResult;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use Illuminate\Support\Collection;

class CheckService implements CheckServiceContract
{
    public const REDIRECT = 5;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var HomoService
     */
    protected $homo;

    /**
     * @var Collection<ProfileServiceContract>
     */
    protected $profile;

    /**
     * @var Collection<ValidatorServiceContract>
     */
    protected $validators;

    public function __construct(ClientInterface $client, HomoServiceContract $homo)
    {
        $this->client = $client;
        $this->homo = $homo;
    }

    /**
     * Set the profiles.
     * @param Collection<ProfileServiceContract> $profiles The Profiles.
     */
    public function setProfiles(Collection $profiles): void
    {
        $this->profiles = $profiles;
    }

    /**
     * Set the validators.
     * @param Collection<ValidatorServiceContract> $validators The Validators.
     */
    public function setValidators(Collection $validators): void
    {
        $this->validators = $validators;
    }

    private function getValidateStatus(Homo $homo, string $status, array $ips, $times): array
    {
        if (!is_array($times)) {
            return [$status, $ips[$homo->getUrl()], $times];
        }

        $offset = array_search($homo->getUrl(), array_keys($ips)) + 1;
        array_splice($ips, $offset);
        array_splice($times, $offset);

        return [$status, array_pop($ips), array_sum($times)];
    }

    /**
     * Validate a user.
     * @param  Homo                     $homo The user.
     * @return Promise\PromiseInterface The Promise.
     */
    protected function validateAsync(Homo $homo): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo) {
            $total_time = 0.0;
            $times = [];
            $ips = [];
            $url = $homo->getUrl();
            try {
                for ($i = 0; $i < static::REDIRECT; ++$i) {
                    $response = yield $this->client->getAsync($url, [
                        RequestOptions::ON_STATS => function (TransferStats $stats) use ($url, &$times, &$total_time, &$ips) {
                            $total_time += $stats->getHandlerStat('total_time') ?? 0;
                            $times[$url] = $stats->getHandlerStat('starttransfer_time') ?? 0;
                            $ips[$url] = $stats->getHandlerStat('primary_ip') ?? null;
                        },
                    ]);
                    foreach ($this->validators as $validator) {
                        if (!$status = $validator->validate($response)) {
                            continue;
                        }
                        return yield $this->getValidateStatus($homo, $status, $ips, $times);
                    }
                    if (!$url = $response->getHeaderLine('Location')) {
                        break;
                    }
                }

                return yield $this->getValidateStatus($homo, ValidationResult::WRONG, $ips, $times);
            } catch (RequestException $e) {
                return yield $this->getValidateStatus($homo, ValidationResult::ERROR, $ips, $total_time);
            }
        });
    }

    /**
     * Create a status object from a user.
     * @param  Homo                     $homo     The user.
     * @param  callable                 $callback The callback that is called after resolution (optional).
     * @return Promise\PromiseInterface The Promise.
     */
    protected function createStatusAsync(Homo $homo, callable $callback = null): Promise\PromiseInterface
    {
        return Promise\coroutine(function () use ($homo, $callback) {
            [[$status, $ip, $duration], $icon] = yield Promise\all([
                $this->validateAsync($homo),
                $this->profiles->get($homo->getService())->getIconAsync($homo->getScreenName()),
            ]);
            $result = new Status(compact(
                'homo',
                'icon',
                'status',
                'ip',
                'duration',
            ));
            if ($callback) {
                $callback($result);
            }
            return yield $result;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $screen_name = null, callable $callback = null): array
    {
        $users = $this->homo->find($screen_name);

        return Pool::batch(
            $this->client,
            array_map(function ($item) use ($callback): callable {
                return function () use ($item, $callback): Promise\PromiseInterface {
                    return $this->createStatusAsync(new Homo($item), $callback);
                };
            }, $users),
            [
                'concurrency' => 4,
            ],
        );
    }
}
