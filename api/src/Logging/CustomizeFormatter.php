<?php
declare(strict_types=1);

namespace HomoChecker\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    protected $dateFormat = 'Y-m-d H:i:s';

    public function __invoke(Logger $logger, ?string $format = null)
    {
        /** @var \Monolog\Logger $logger */
        foreach ($logger->getHandlers() as $handler) {
            /** @var \Monolog\Handler\HandlerWrapper $handler */
            $handler->setFormatter(new LineFormatter($format, $this->dateFormat, true, true));
        }
    }
}
