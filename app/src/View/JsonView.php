<?php
namespace HomoChecker\View;

class JsonView implements ViewInterface
{
    public function __construct()
    {
        header('Content-Type: application/json');
    }

    public function render($data)
    {
        echo json_encode($data);
    }
}
