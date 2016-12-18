<?php
namespace HomoChecker\Test\Mock\Utilities;

use Slim\Container;
use HomoChecker\Test\Mock\Model\MockCheck;

class MockContainer extends Container
{
    public $checker;
    public $icon;
    public $target;
    public $validators = [];
    public $client;

    public function __construct()
    {
        parent::__construct([
            'settings' => [
                'outputBuffering'        => false,
                'addContentLengthHeader' => false,
            ],
        ]);
    }
}
