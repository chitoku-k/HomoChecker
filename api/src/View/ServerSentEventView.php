<?php
declare(strict_types=1);

namespace HomoChecker\View;

use HomoChecker\Contracts\View\ServerSentEventView as ServerSentEventViewContract;

class ServerSentEventView implements ServerSentEventViewContract
{
    public $event;

    public function __construct($event)
    {
        $this->event = $event;

        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            header('Content-Type: text/event-stream');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @codeCoverageIgnore
     */
    public function render($data, $event = null): void
    {
        $this->output(
            'event: ' . ($event ?? $this->event) . "\r\n" .
            'data: ' . json_encode($data)
        );
    }

    public function close(): void
    {
        $this->output('');
    }

    public function output(string $chunk): int
    {
        echo sprintf("%x\r\n", $length = strlen($chunk));
        echo $chunk . "\r\n\r\n";

        @ob_flush();
        flush();

        return $length;
    }
}
