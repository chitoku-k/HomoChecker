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
             ->once()
             ->andReturn('');

        $actual = new NonBufferedBody($base);

        $this->assertEquals('', $actual->__toString());
    }

    public function testClose(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('close')
             ->once()
             ->andReturn();

        $actual = new NonBufferedBody($base);
        $actual->close();
    }

    public function testDetach(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('detach')
             ->once()
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
             ->once()
             ->andReturn(0);

        $actual = new NonBufferedBody($base);

        $this->assertEquals(0, $actual->tell());
    }

    public function testEof(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('eof')
             ->once()
             ->andReturn(true);

        $actual = new NonBufferedBody($base);

        $this->assertTrue($actual->eof());
    }

    public function testIsSeekable(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('isSeekable')
             ->once()
             ->andReturn(false);

        $actual = new NonBufferedBody($base);

        $this->assertFalse($actual->isSeekable());
    }

    public function testSeek(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('seek')
             ->once()
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
             ->once()
             ->andReturn();

        $actual = new NonBufferedBody($base);
        $actual->rewind();
    }

    public function testIsWritable(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('isWritable')
             ->once()
             ->andReturn(true);

        $actual = new NonBufferedBody($base);

        $this->assertTrue($actual->isWritable());
    }

    public function testWrite(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('write')
             ->once()
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
             ->once()
             ->andReturn(false);

        $actual = new NonBufferedBody($base);

        $this->assertFalse($actual->isReadable());
    }

    public function testRead(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('read')
             ->once()
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
             ->once()
             ->andReturn('');

        $actual = new NonBufferedBody($base);

        $this->assertEquals('', $actual->getContents());
    }

    public function testGetMetadata(): void
    {
        /** @var MockInterface&Psr7NonBufferedBody $base */
        $base = m::mock(Psr7NonBufferedBody::class);
        $base->shouldReceive('getMetadata')
             ->once()
             ->withArgs([null])
             ->andReturn(null);

        $actual = new NonBufferedBody($base);

        $this->assertNull($actual->getMetadata());
    }
}
