<?php
declare(strict_types=1);

namespace HomoChecker\Test\Http;

use HomoChecker\Http\NonBufferedBody;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\NonBufferedBody as Psr7NonBufferedBody;

class NonBufferedBodyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     */
    public function testWrite(): void
    {
        /** @var MockInterface|Psr7NonBufferedBody $base */
        $base = m::mock('overload:' . Psr7NonBufferedBody::class);
        $base->size = 0;
        $base->shouldReceive('write')
             ->withArgs(['test'])
             ->andReturn(4);

        $actual = new NonBufferedBody();

        $this->assertEquals(0, $actual->getSize());
        $actual->write('test');
        $this->assertEquals(4, $actual->getSize());
    }
}
