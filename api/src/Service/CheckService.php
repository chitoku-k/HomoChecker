<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
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
use HomoChecker\Domain\Result;
use HomoChecker\Domain\Status;
use HomoChecker\Domain\Validator\ValidationResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Prometheus\Counter;

class CheckService implements CheckServiceContract
{
    /**
     * @param Collection<string, ProfileServiceContract> $profiles
     * @param Collection<int, ValidatorServiceContract>  $validators
     */
    public function __construct(
        protected ClientServiceContract $client,
        protected HomoServiceContract $homo,
        protected Counter $checkCounter,
        protected Counter $checkErrorCounter,
        protected Collection $profiles,
        protected Collection $validators,
    ) {}

    /**
     * Validate a user.
     * @param  Homo                     $homo The user.
     * @return PromiseInterface<Result> The Promise.
     */
    protected function validateAsync(Homo $homo): PromiseInterface
    {
        $url = $homo->getUrl();
        if (!$url) {
            throw new \InvalidArgumentException('Invalid URL');
        }

        return Coroutine::of(function () use ($url) {
            $total_time = 0.0;
            $total_starttransfer_time = 0.0;
            $code = null;
            $http = null;
            $certificates = null;
            $ip = null;

            try {
                foreach ($this->client->getRedirectsAsync($url) as $url => $promise) {
                    /** @var Response $response */
                    $response = yield $promise;

                    foreach ($this->validators as $validator) {
                        $total_time += $response->getTotalTime();
                        $total_starttransfer_time += $response->getStartTransferTime();
                        $code = collect([$response->getStatusCode(), $response->getReasonPhrase()])->join(' ');
                        $http = $response->getHttpVersion();
                        $certificates = $response->getCertificates();
                        $ip = $response->getPrimaryIP();

                        if (!$status = $validator->validate($response)) {
                            continue;
                        }

                        return yield new Result([
                            'status' => $status,
                            'code' => $code,
                            'http' => $http,
                            'certificates' => $certificates,
                            'ip' => $ip,
                            'url' => $url,
                            'duration' => $total_starttransfer_time,
                        ]);
                    }
                }

                return yield new Result([
                    'status' => ValidationResult::WRONG,
                    'code' => $code ?? null,
                    'http' => $http,
                    'certificates' => $certificates,
                    'ip' => $ip,
                    'url' => $url,
                    'duration' => $total_starttransfer_time,
                ]);
            } catch (ConnectException|RequestException $e) {
                Log::debug($e);

                return yield new Result([
                    'status' => ValidationResult::ERROR,
                    'code' => $code ?? null,
                    'http' => $http,
                    'certificates' => $certificates,
                    'ip' => $ip,
                    'url' => $url,
                    'duration' => $total_time,
                    'error' => $e->getHandlerContext()['error'] ?? null,
                ]);
            } catch (\Throwable $e) {
                Log::error($e);

                return yield new Result([
                    'status' => ValidationResult::ERROR,
                    'code' => $code ?? null,
                    'http' => $http,
                    'certificates' => $certificates,
                    'ip' => $ip,
                    'url' => $url,
                    'duration' => $total_time,
                ]);
            }
        });
    }

    /**
     * Create a status object from a user.
     * @param  Homo                     $homo     The user.
     * @param  callable                 $callback The callback that is called after resolution (optional).
     * @return PromiseInterface<Status> The Promise.
     */
    protected function createStatusAsync(Homo $homo, ?callable $callback = null): PromiseInterface
    {
        return Coroutine::of(function () use ($homo, $callback) {
            $service = $homo->getService();
            $screen_name = $homo->getScreenName();

            /**
             * @var Result  $result
             * @var ?string $icon
             */
            [$result, $icon] = yield Utils::all([
                $this->validateAsync($homo),
                $homo->getProfile()?->getIconUrl() ?? $this->profiles->get($service)?->getIconAsync($screen_name),
            ]);
            $status = new Status([
                'homo' => $homo,
                'result' => $result,
                'icon' => $icon,
            ]);

            if ($callback) {
                $callback($status);
            }

            $this->checkCounter->inc([
                'status' => (string) $result->getStatus()?->value,
                'code' => (string) (int) $result->getCode(),
                'screen_name' => $homo->getScreenName(),
                'url' => $homo->getUrl(),
            ]);

            if ($result->getStatus() === ValidationResult::ERROR) {
                $this->checkErrorCounter->inc([
                    'status' => (string) $result->getStatus()?->value,
                    'code' => (string) (int) $result->getCode(),
                    'screen_name' => $homo->getScreenName(),
                    'url' => $homo->getUrl(),
                ]);
            }

            return yield $status;
        });
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function execute(?string $screen_name = null, ?callable $callback = null): array
    {
        return Pool::batch(
            $this->client,
            collect($this->homo->find($screen_name))
                ->mapInto(Homo::class)
                ->map(fn (Homo $item) => fn () => $this->createStatusAsync($item, $callback))
                ->toArray(),
            [
                'concurrency' => 4,
            ],
        );
    }
}
