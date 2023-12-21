<?php
declare(strict_types=1);

namespace HomoChecker\Http;

use Slim\Psr7\NonBufferedBody as NonBufferedBodyBase;

// Slim\Psr7\Message::withHeader() requires $body to be a subclass of
// Slim\Psr7\NonBufferedBody in order to get \header() to be called.
class NonBufferedBody extends NonBufferedBodyBase
{
    protected int $size = 0;

    public function __construct(protected NonBufferedBodyBase $base) {}

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->base->__toString();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function close(): void
    {
        $this->base->close();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function detach()
    {
        return $this->base->detach();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function tell(): int
    {
        return $this->base->tell();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function eof(): bool
    {
        return $this->base->eof();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isSeekable(): bool
    {
        return $this->base->isSeekable();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function seek($offset, $whence = SEEK_SET): void
    {
        $this->base->seek($offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function rewind(): void
    {
        $this->base->rewind();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isWritable(): bool
    {
        return $this->base->isWritable();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function write($string): int
    {
        $size = $this->base->write($string);
        $this->size += $size;
        return $size;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function isReadable(): bool
    {
        return $this->base->isReadable();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function read($length): string
    {
        return $this->base->read($length);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getContents(): string
    {
        return $this->base->getContents();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getMetadata($key = null): ?array
    {
        return $this->base->getMetadata($key);
    }
}
