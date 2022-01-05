<?php
declare(strict_types=1);

namespace HomoChecker\Domain;

use HomoChecker\Domain\Validator\ValidationResult;

class Result
{
    /**
     * @var ?ValidationResult The status.
     */
    protected ?ValidationResult $status;

    /**
     * @var ?string The status code and reason phrase.
     */
    protected ?string $code;

    /**
     * @var ?string The HTTP version.
     */
    protected ?string $http;

    /**
     * @var ?string The IP address.
     */
    protected ?string $ip;

    /**
     * @var ?string The URL.
     */
    protected ?string $url;

    /**
     * @var ?float The duration.
     */
    protected ?float $duration;

    /**
     * @var ?string The error message.
     */
    protected ?string $error;

    public function __construct(array|object $result = null)
    {
        $result = (object) $result;

        $this->setStatus($result->status ?? null);
        $this->setCode($result->code ?? null);
        $this->setHttp($result->http ?? null);
        $this->setIp($result->ip ?? null);
        $this->setUrl($result->url ?? null);
        $this->setDuration($result->duration ?? null);
        $this->setError($result->error ?? null);
    }

    /**
     * Get the status that represents the result of the response.
     * @return ?ValidationResult The status that represents the result of the response.
     */
    public function getStatus(): ?ValidationResult
    {
        return $this->status;
    }

    /**
     * Set the status that represents the result of the response.
     * @param ?ValidationResult $status The status that represents the result of the response.
     */
    public function setStatus(?ValidationResult $status): void
    {
        $this->status = $status;
    }

    /**
     * Get the status code and reason phrase of the response.
     * @return ?string The status code and reason phrase of the response.
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Set the status code and reason phrase of the response.
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * Get the HTTP version used to serve a response.
     * @return ?string The HTTP version used to serve a response.
     */
    public function getHttp(): ?string
    {
        return $this->http;
    }

    /**
     * Set the HTTP version used to serve a response.
     * @param ?string $http The HTTP version used to serve a response.
     */
    public function setHttp(?string $http): void
    {
        $this->http = $http;
    }

    /**
     * Get the IP address to which the server sent a response.
     * @return ?string The IP address to which the server sent a response.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * Set the IP address to which the server sent a response.
     * @param ?string $ip The IP address to which the server sent a response.
     */
    public function setIp(?string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * Get the URL.
     * @return ?string The URL.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set the URL.
     * @param ?string $url The URL.
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * Get the duration of the response.
     * @return ?float The duration of the response.
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * Set the duration of the response.
     * @param ?float $duration The duration of the response.
     */
    public function setDuration(?float $duration): void
    {
        $this->duration = $duration;
    }

    /**
     * Get the error message.
     * @return ?string The error message.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Set the error message.
     * @param ?string $error The error message.
     */
    public function setError(?string $error): void
    {
        $this->error = $error;
    }
}
