<?php
declare(strict_types=1);

namespace HomoChecker\Action;

use Prometheus\RegistryInterface;
use Prometheus\RendererInterface;
use Slim\Http\Response as Response;
use Slim\Http\ServerRequest as Request;

class MetricsAction
{
    public function __construct(protected RegistryInterface $registry, protected RendererInterface $format)
    {
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        $response = $response->withHeader('Content-Type', 'text/plain');
        $response->getBody()->write($this->format->render($this->registry->getMetricFamilySamples()));
        return $response;
    }
}
