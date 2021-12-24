<?php
declare(strict_types=1);

namespace HomoChecker\Http;

use Slim\Psr7\NonBufferedBody as NonBufferedBodyBase;

// Slim\Psr7\Message::withHeader() requires $body to be a subclass of
// Slim\Psr7\NonBufferedBody in order to get \header() to be called.
class NonBufferedBody extends NonBufferedBodyBase
{
    protected int $size = 0;

    public function __construct(protected NonBufferedBodyBase $base)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->base->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->base->close();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return $this->base->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->base->tell();
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->base->eof();
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->base->isSeekable();
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->base->seek($offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->base->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return $this->base->isWritable();
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $size = $this->base->write($string);
        $this->size += $size;
        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->base->isReadable();
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        return $this->base->read($length);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return $this->base->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null): ?array
    {
        return $this->base->getMetadata($key);
    }
}
