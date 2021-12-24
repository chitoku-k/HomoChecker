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

    public function testToString(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('__toString')
             ->andReturn('');

        $actual = new NonBufferedBody($base);

        $this->assertEquals('', $actual->__toString());
    }

    public function testClose(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('close')
             ->andReturn();

        $actual = new NonBufferedBody($base);
        $actual->close();
    }

    public function testDetach(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('detach')
             ->andReturn(null);

        $actual = new NonBufferedBody($base);

        $this->assertNull($actual->detach());
    }

    public function testGetSize(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);

        $actual = new NonBufferedBody($base);

        $this->assertEquals(0, $actual->getSize());
    }

    public function testTell(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('tell')
             ->andReturn(0);

        $actual = new NonBufferedBody($base);

        $this->assertEquals(0, $actual->tell());
    }

    public function testEof(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('eof')
             ->andReturn(true);

        $actual = new NonBufferedBody($base);

        $this->assertTrue($actual->eof());
    }

    public function testIsSeekable(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('isSeekable')
             ->andReturn(false);

        $actual = new NonBufferedBody($base);

        $this->assertFalse($actual->isSeekable());
    }

    public function testSeek(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('seek')
             ->withArgs([0, SEEK_SET])
             ->andReturn();

        $actual = new NonBufferedBody($base);
        $actual->seek(0, SEEK_SET);
    }

    public function testRewind(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('rewind')
             ->andReturn();

        $actual = new NonBufferedBody($base);
        $actual->rewind();
    }

    public function testIsWritable(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('isWritable')
             ->andReturn(true);

        $actual = new NonBufferedBody($base);

        $this->assertTrue($actual->isWritable());
    }

    public function testWrite(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->size = 0;
        $base->shouldReceive('write')
             ->withArgs(['test'])
             ->andReturn(4);

        $actual = new NonBufferedBody($base);

        $this->assertEquals(0, $actual->getSize());
        $actual->write('test');
        $this->assertEquals(4, $actual->getSize());
    }

    public function testIsReadable(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('isReadable')
             ->andReturn(false);

        $actual = new NonBufferedBody($base);

        $this->assertFalse($actual->isReadable());
    }

    public function testRead(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('read')
             ->withArgs([4])
             ->andReturn('homo');

        $actual = new NonBufferedBody($base);

        $this->assertEquals('homo', $actual->read(4));
    }

    public function testGetContents(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('getContents')
             ->andReturn('');

        $actual = new NonBufferedBody($base);

        $this->assertEquals('', $actual->getContents());
    }

    public function testGetMetadata(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('getMetadata')
             ->withArgs([null])
             ->andReturn(null);

        $actual = new NonBufferedBody($base);

        $this->assertNull($actual->getMetadata());
    }
}
