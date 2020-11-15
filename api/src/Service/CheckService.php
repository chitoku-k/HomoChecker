<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\HomoService as HomoServiceContract;
use HomoChecker\Contracts\Service\ProfileService as ProfileServiceContract;
use HomoChecker\Contracts\Service\ValidatorService as ValidatorServiceContract;
use HomoChecker\Domain\Homo;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckService implements CheckServiceContract
{
    public const REDIRECT = 5;

    protected ClientInterface $client;
    protected HomoServiceContract $homo;

    /**
     * @var Collection<ProfileServiceContract>
     */
    protected ?Collection $profiles;

    /**
     * @var Collection<ValidatorServiceContract>
     */
    protected ?Collection $validators;

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
     * @param  Homo             $homo The user.
     * @return PromiseInterface The Promise.
     */
    protected function validateAsync(Homo $homo): PromiseInterface
    {
        return Coroutine::of(function () use ($homo) {
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
            } catch (GuzzleException $e) {
                return yield $this->getValidateStatus($homo, ValidationResult::ERROR, $ips, $total_time);
            } catch (Throwable $e) {
                Log::error($e);
            }
        });
    }

    /**
     * Create a status object from a user.
     * @param  Homo             $homo     The user.
     * @param  callable         $callback The callback that is called after resolution (optional).
     * @return PromiseInterface The Promise.
     */
    protected function createStatusAsync(Homo $homo, callable $callback = null): PromiseInterface
    {
        return Coroutine::of(function () use ($homo, $callback) {
            [[$status, $ip, $duration], $icon] = yield Utils::all([
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
            array_map(fn ($item) => fn () => $this->createStatusAsync(new Homo($item), $callback), $users),
            [
                'concurrency' => 4,
            ],
        );
    }
}
