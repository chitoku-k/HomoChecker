<?php
namespace HomoChecker\View;

class ServerSentEventView implements ViewInterface {
    public $event;

    public function __construct($event) {
        $this->event = $event;

        header('Content-Type: text/event-stream');

        // 2 KiB padding
        echo ":" . str_repeat(" ", 2048) . "\n";

        flush();
    }

    public function render($data) {
        echo "event: {$this->event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        flush();
    }
}
