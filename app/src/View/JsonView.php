<?php
namespace HomoChecker\View;

class JsonView implements ViewInterface {
    public function __construct() {
        header('Content-Type: application/json');
        echo str_repeat(' ', 2048);
        flush();
    }

    public function render($data) {
        echo json_encode($data) . "\n";
        flush();
    }
}
