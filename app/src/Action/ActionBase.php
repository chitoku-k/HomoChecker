<?php
namespace HomoChecker\Action;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

abstract class ActionBase
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(...$params)
    {
        return $this->route(...$params);
    }

    abstract public function route(Request $request, Response $response, array $args);
}
