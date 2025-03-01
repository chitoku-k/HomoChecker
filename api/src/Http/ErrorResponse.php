<?php
declare(strict_types=1);

namespace HomoChecker\Http;

use Psr\Http\Message\StreamInterface;
use Slim\Http\Response;

final class ErrorResponse extends Response
{
    protected ?\Throwable $exception = null;

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    public function withException(\Throwable $exception): static
    {
        $response = new self($this, $this->streamFactory);
        $response->exception = $exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withAddedHeader($name, $value): static
    {
        $response = new static(parent::withAddedHeader($name, $value), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withBody(StreamInterface $body): static
    {
        $response = new static(parent::withBody($body), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withHeader($name, $value): static
    {
        $response = new static(parent::withHeader($name, $value), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withoutHeader($name): static
    {
        $response = new static(parent::withoutHeader($name), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withProtocolVersion($version): static
    {
        $response = new static(parent::withProtocolVersion($version), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withStatus($code, $reasonPhrase = ''): static
    {
        $response = new static(parent::withStatus($code, $reasonPhrase), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withJson($data, ?int $status = null, int $options = 0, int $depth = 512): static
    {
        $response = new static(parent::withJson($data, $status, $options, $depth), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withRedirect(string $url, ?int $status = null): static
    {
        $response = new static(parent::withRedirect($url, $status), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withFileDownload($file, ?string $name = null, $contentType = true): static
    {
        $response = new static(parent::withFileDownload($file, $name, $contentType), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function withFile($file, $contentType = true): static
    {
        $response = new static(parent::withFile($file, $contentType), $this->streamFactory);
        $response->exception = $this->exception;
        return $response;
    }
}
