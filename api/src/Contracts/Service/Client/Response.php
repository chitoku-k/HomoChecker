<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\Service\Client;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    /**
     * @var float The total time.
     */
    protected float $totalTime = 0.0;

    /**
     * @var float The start transfer time.
     */
    protected float $startTransferTime = 0.0;

    /**
     * @var string[][] The TLS certificates.
     */
    protected array $certificates = [];

    /**
     * @var ?string The HTTP version.
     */
    protected ?string $httpVersion = null;

    /**
     * @var ?string The primary IP.
     */
    protected ?string $primaryIP = null;

    public function __construct(protected ResponseInterface $response)
    {
    }

    /**
     * Get the total time.
     * @return float The total time.
     */
    public function getTotalTime(): float
    {
        return $this->totalTime;
    }

    /**
     * Set the total time.
     * @param float $totalTime The total time.
     */
    public function setTotalTime(float $totalTime): void
    {
        $this->totalTime = $totalTime;
    }

    /**
     * Get the start transfer time.
     * @return float The start transfer time.
     */
    public function getStartTransferTime(): float
    {
        return $this->startTransferTime;
    }

    /**
     * Set the start transfer time.
     * @param float $startTransferTime The start transfer time.
     */
    public function setStartTransferTime(float $startTransferTime): void
    {
        $this->startTransferTime = $startTransferTime;
    }

    /**
     * Get the TLS certificates.
     * @return string[][] The TLS certificates.
     */
    public function getCertificates(): array
    {
        return $this->certificates;
    }

    /**
     * Set the TLS certificates.
     * @param string[][] The TLS certificates.
     */
    public function setCertificates(array $certificates): void
    {
        $this->certificates = collect($certificates)
            ->map(fn ($cerificate) => [
                'subject' => $cerificate['Subject'] ?? '',
                'issuer' => $cerificate['Issuer'] ?? '',
                'subjectAlternativeName' => str($cerificate['X509v3 Subject Alternative Name'] ?? '')
                    ->split('/,\s*/', -1, \PREG_SPLIT_NO_EMPTY)
                    ->map(fn (string $name) => str($name)->replaceFirst('DNS:', ''))
                    ->all(),
                'notBefore' => $cerificate['Start date'] ?? '',
                'notAfter' => $cerificate['Expire date'] ?? '',
            ])
            ->all();
    }

    /**
     * Get the HTTP version.
     * @return ?string The HTTP Version.
     */
    public function getHttpVersion(): ?string
    {
        return $this->httpVersion;
    }

    /**
     * Set the HTTP version.
     */
    public function setHttpVersion(null|int|string $httpVersion): void
    {
        $this->httpVersion = match ($httpVersion) {
            CURL_HTTP_VERSION_1_0 => '1.0',
            CURL_HTTP_VERSION_1_1 => '1.1',
            CURL_HTTP_VERSION_2 => '2',
            CURL_HTTP_VERSION_3 => '3',
            default => null,
        };
    }

    /**
     * Get the primary IP.
     * @return ?string The primary IP.
     */
    public function getPrimaryIP(): ?string
    {
        return $this->primaryIP;
    }

    /**
     * Set the primary IP.
     * @param ?string $primaryIP The primary IP.
     */
    public function setPrimaryIP(?string $primaryIP): void
    {
        $this->primaryIP = $primaryIP;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function withStatus($code, $reasonPhrase = ''): static
    {
        $self = clone $this;
        $self->response = $self->response->withStatus($code, $reasonPhrase);
        return $self;
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function withProtocolVersion($version): static
    {
        $self = clone $this;
        $self->response = $self->response->withProtocolVersion($version);
        return $self;
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function withHeader($name, $value): static
    {
        $self = clone $this;
        $self->response = $self->response->withHeader($name, $value);
        return $self;
    }

    public function withAddedHeader($name, $value): static
    {
        $self = clone $this;
        $self->response = $self->response->withAddedHeader($name, $value);
        return $self;
    }

    public function withoutHeader($name): static
    {
        $self = clone $this;
        $self->response = $self->response->withoutHeader($name);
        return $self;
    }

    public function getBody(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function withBody(StreamInterface $body): static
    {
        $self = clone $this;
        $self->response = $self->response->withBody($body);
        return $self;
    }
}
