<?php
namespace HomoChecker\Model\Validator;

use Interop\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseInterface as Response;

abstract class ValidatorBase
{
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke(Response $response)
    {
        return $this->validate($response);
    }

    abstract protected function validate(Response $response);
}
