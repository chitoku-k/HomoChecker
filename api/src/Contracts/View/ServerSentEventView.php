<?php
declare(strict_types=1);

namespace HomoChecker\Contracts\View;

interface ServerSentEventView extends View
{
    /**
     * Close the stream.
     */
    public function close(): void;

    /**
     * Print the output chunk by chunk.
     */
    public function output(string $chunk): int;
}
