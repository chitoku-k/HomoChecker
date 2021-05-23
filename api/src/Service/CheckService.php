<?php
declare(strict_types=1);

namespace HomoChecker\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
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

    /**
     * Validate a user.
     * @param  Homo             $homo The user.
     * @return PromiseInterface The Promise.
     */
    protected function validateAsync(Homo $homo): PromiseInterface
    {
        return Coroutine::of(function () use ($homo) {
            $total_time = 0.0;
            $total_starttransfer_time = 0.0;
            $ip = null;
            $code = null;

            try {
                foreach ($this->client->getAsync($homo->getUrl()) as $url => $promise) {
                    /** @var Response $response */
                    $response = yield $promise;

                    foreach ($this->validators as $validator) {
                        $total_time += $response->getTotalTime();
                        $total_starttransfer_time += $response->getStartTransferTime();
                        $ip = $response->getPrimaryIP();
                        $code = collect([$response->getStatusCode(), $response->getReasonPhrase()])->join(' ');

                        if (!$status = $validator->validate($response)) {
                            continue;
                        }

                        return yield new Result([
                            'status' => $status,
                            'code' => $code,
                            'ip' => $ip,
                            'url' => $url,
                            'duration' => $total_starttransfer_time,
                        ]);
                    }
                }

                return yield new Result([
                    'status' => ValidationResult::WRONG,
                    'code' => $code ?? null,
                    'ip' => $ip,
                    'url' => $url ?? null,
                    'duration' => $total_starttransfer_time,
                ]);
            } catch (ConnectException|RequestException $e) {
                Log::debug($e);

                return yield new Result([
                    'status' => ValidationResult::ERROR,
                    'code' => $code ?? null,
                    'ip' => $ip,
                    'url' => $url ?? null,
                    'duration' => $total_time,
                    'error' => $e->getHandlerContext()['error'] ?? null,
                ]);
            } catch (GuzzleException $e) {
                Log::debug($e);

                return yield new Result([
                    'status' => ValidationResult::ERROR,
                    'code' => $code ?? null,
                    'ip' => $ip,
                    'url' => $url ?? null,
                    'duration' => $total_time,
                ]);
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
            [$result, $icon] = yield Utils::all([
                $this->validateAsync($homo),
                $this->profiles->get($homo->getService())->getIconAsync($homo->getScreenName()),
            ]);
            $status = new Status([
                'homo' => $homo,
                'result' => $result,
                'icon' => $icon,
            ]);
            if ($callback) {
                $callback($status);
            }
            return yield $status;
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
