<?php
declare(strict_types=1);

namespace HomoChecker\Middleware;

use Middlewares\AccessLog;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class AccessLogMiddleware extends AccessLog
{
    /**
     * @var string[] Paths to skip logging.
     */
    protected array $skipPaths;

    public function __construct(LoggerInterface $logger, array $skipPaths = [])
    {
        parent::__construct($logger);

        $this->skipPaths = $skipPaths;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (collect($this->skipPaths)->contains($request->getUri()->getPath())) {
            return $handler->handle($request);
        }

        return parent::process($request, $handler);
    }
}
