<?php
declare(strict_types=1);

namespace HomoChecker\Middleware;

use Middlewares\AccessLog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccessLogMiddleware implements MiddlewareInterface
{
    /**
     * @var AccessLog The base access log.
     */
    protected AccessLog $base;

    /**
     * @var string[] Paths to skip logging.
     */
    protected array $skipPaths;

    public function __construct(AccessLog $base, array $skipPaths = [])
    {
        $this->base = $base;
        $this->skipPaths = $skipPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (collect($this->skipPaths)->contains($request->getUri()->getPath())) {
            return $handler->handle($request);
        }

        return $this->base->process($request, $handler);
    }
}
