<?php
declare(strict_types=1);

namespace HomoChecker\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerWrapper;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class CustomizeFormatter
{
    protected string $dateFormat = 'Y-m-d H:i:s';

    public function __invoke(LoggerInterface $logger, ?string $format = null)
    {
        /** @var Logger $logger */
        foreach ($logger->getHandlers() as $handler) {
            /** @var HandlerWrapper $handler */
            $handler->setFormatter(new LineFormatter($format, $this->dateFormat, true, true));
        }
    }
}
