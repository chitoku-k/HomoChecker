<?php
declare(strict_types=1);

namespace HomoChecker\Test\Logging;

use HomoChecker\Logging\CustomizeFormatter;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerWrapper;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CustomizeFormatterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInvoke(): void
    {
        /** @var LineFormatter $formatter1 */
        /** @var HandlerWrapper&MockInterface $handler1 */
        $handler1 = m::mock(HandlerWrapper::class);
        $handler1->shouldReceive('setFormatter')
                 ->with(m::capture($formatter1));

        /** @var LineFormatter $formatter2 */
        /** @var HandlerWrapper&MockInterface $handler2 */
        $handler2 = m::mock(HandlerWrapper::class);
        $handler2->shouldReceive('setFormatter')
                 ->with(m::capture($formatter2));

        /** @var LoggerInterface&MockInterface $logger */
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('getHandlers')
               ->andReturn([$handler1, $handler2]);

        $actual = new CustomizeFormatter();
        $actual($logger, "[%datetime%] %level_name%: %message% %context% %extra%\n");

        $this->assertInstanceOf(LineFormatter::class, $formatter1);
        $this->assertInstanceOf(LineFormatter::class, $formatter2);

        $this->assertEquals("[2001-02-03 04:05:06] DEBUG: Test message {\"url\":\"/healthz\"} \n", $formatter1->format(
            new LogRecord(
                new \DateTimeImmutable('2001-02-03 04:05:06'),
                'default',
                Level::Debug,
                'Test message',
                [
                    'url' => '/healthz',
                ],
            ),
        ));
        $this->assertEquals("[2001-02-03 04:05:06] DEBUG: Test message {\"url\":\"/healthz\"} \n", $formatter2->format(
            new LogRecord(
                new \DateTimeImmutable('2001-02-03 04:05:06'),
                'default',
                Level::Debug,
                'Test message',
                [
                    'url' => '/healthz',
                ],
            ),
        ));
    }
}
