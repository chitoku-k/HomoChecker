<?php
declare(strict_types=1);

namespace HomoChecker\Http;

use Slim\Psr7\NonBufferedBody as NonBufferedBodyBase;

class NonBufferedBody extends NonBufferedBodyBase
{
    protected int $size = 0;

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
    public function write($string): int
    {
        $size = parent::write($string);
        $this->size += $size;
        return $size;
    }
}
