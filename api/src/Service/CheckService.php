<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\Coroutine;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;
use HomoChecker\Contracts\Service\CheckService as CheckServiceContract;
use HomoChecker\Contracts\Service\Client\Response;
use HomoChecker\Contracts\Service\ClientService as ClientServiceContract;
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
    /**
     * @var Collection<ProfileServiceContract>
     */
    protected ?Collection $profiles;

    /**
     * @var Collection<ValidatorServiceContract>
     */
    protected ?Collection $validators;

    public function __construct(protected ClientServiceContract $client, protected HomoServiceContract $homo)
    {
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

    private function getValidateStatus(Homo $homo, string $status, array $ips, array|float $times): array
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

            try {
                foreach ($this->client->getAsync($homo->getUrl()) as $url => $promise) {
                    /** @var Response $response */
                    $response = yield $promise;

                    foreach ($this->validators as $validator) {
                        $total_time += $response->getTotalTime();
                        $times[$url] = $response->getStartTransferTime();
                        $ips[$url] = $response->getPrimaryIP();

                        if (!$status = $validator->validate($response)) {
                            continue;
                        }
                        return yield $this->getValidateStatus($homo, $status, $ips, $times);
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
        return Pool::batch(
            $this->client,
            collect($this->homo->find($screen_name))
                ->map(fn (\stdClass $item) => new Homo($item))
                ->map(fn (Homo $item) => fn () => $this->createStatusAsync($item, $callback))
                ->toArray(),
            [
                'concurrency' => 4,
            ],
        );
    }
}
