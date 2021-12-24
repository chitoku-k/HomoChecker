<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service\Client;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Altsvc
{
    /**
     * @var bool Invalidates all alternatives.
     */
    protected bool $clear = false;

    /**
     * @var ?string The identifier for the protocol (e.g., h3).
     */
    protected ?string $protocolId;

    /**
     * @var ?string The alternative authority (e.g., :443).
     */
    protected ?string $altAuthority;

    /**
     * @var float The max age.
     */
    protected float $maxAge = 86400;

    public function __construct(string $value)
    {
        /** @var string[] $params */
        $params = Str::of($value)
            ->trim()
            ->split('/\s*;\s*/')
            ->map(fn (string $param) => Str::of($param)->split('/=/'))
            ->map(fn (Collection $param) => $param->map(fn (string $v) => (string) Str::of($v)->trim('"'))->pad(2, '')->toArray())
            ->toArray();

        foreach ($params as [$key, $value]) {
            switch ($key) {
                case 'clear':
                    $this->clear = true;
                    break;
                case 'ma':
                    $this->maxAge = (float) $value;
                    break;
                case 'persist':
                    break;
                default:
                    $this->protocolId = $key;
                    $this->altAuthority = $value;
                    break;
            }
        }
    }

    /**
     * Get the value indicating whether to invalidate all alternatives.
     * @return bool Whether to invalidate all alternatives.
     */
    public function isClear(): bool
    {
        return $this->clear;
    }

    /**
     * Get the identifier for the protocol.
     * @return ?string The identifier for the protocol.
     */
    public function getProtocolId(): ?string
    {
        return $this->protocolId;
    }

    /**
     * Get the alternative authority.
     * @return ?string The alternative authority.
     */
    public function getAltAuthority(): ?string
    {
        return $this->altAuthority;
    }

    /**
     * Get the max age.
     * @return float The max age.
     */
    public function getMaxAge(): float
    {
        return $this->maxAge;
    }
}
