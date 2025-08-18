<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Stream;

use PhoneBurner\Pinch\Component\Http\Stream\FileStream;
use PhoneBurner\Pinch\Filesystem\FileMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FileStreamTest extends TestCase
{
    private string $temp_file;

    protected function setUp(): void
    {
        $this->temp_file = \tempnam(\sys_get_temp_dir(), 'test');
        \file_put_contents($this->temp_file, 'test content');
    }

    protected function tearDown(): void
    {
        if (\file_exists($this->temp_file)) {
            \unlink($this->temp_file);
        }
    }

    #[Test]
    public function constructor(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        self::assertTrue($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertTrue($stream->isSeekable());
        self::assertNotNull($stream->getSize());
    }

    #[Test]
    public function readWriteOperations(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::WriteCreateOrTruncateExisting);
        self::assertTrue($stream->isWritable());

        $bytes_written = $stream->write('new content');
        self::assertSame(11, $bytes_written);
        $stream->close();

        $stream = new FileStream($this->temp_file, FileMode::Read);
        self::assertSame('new content', $stream->getContents());
    }

    #[Test]
    public function readChunks(): void
    {
        $content = \str_repeat('test', 1000);
        \file_put_contents($this->temp_file, $content);

        $stream = new FileStream($this->temp_file, FileMode::Read);
        $chunk = $stream->read(100);
        self::assertSame(100, \strlen($chunk));
        self::assertStringStartsWith('test', $chunk);
    }

    #[Test]
    public function seekAndTell(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        self::assertSame(0, $stream->tell());

        $stream->seek(5);
        self::assertSame(5, $stream->tell());

        $stream->rewind();
        self::assertSame(0, $stream->tell());
    }

    #[Test]
    public function eof(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        self::assertFalse($stream->eof());

        $stream->getContents();
        self::assertTrue($stream->eof());
    }

    #[Test]
    public function getSize(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        self::assertSame(12, $stream->getSize());

        $stream->close();
        self::assertNull($stream->getSize());
    }

    #[Test]
    public function toStringReturnsExpectedValue(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        self::assertSame('test content', (string)$stream);

        $stream->close();
        self::assertSame('', (string)$stream);
    }

    #[Test]
    public function detach(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        $resource = $stream->detach();

        self::assertNotNull($resource);
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isSeekable());
        self::assertNull($stream->getSize());

        \fclose($resource);
    }

    #[Test]
    public function getMetadata(): void
    {
        $stream = new FileStream($this->temp_file, FileMode::Read);
        $metadata = $stream->getMetadata();

        self::assertIsArray($metadata);
        self::assertTrue($metadata['seekable']);
        self::assertContains($metadata['stream_type'], ['plainfile', 'STDIO']);

        self::assertTrue($stream->getMetadata('seekable'));
        self::assertNull($stream->getMetadata('non_existent_key'));
    }

    #[Test]
    public function writeToReadOnlyStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable');

        $stream = new FileStream($this->temp_file, FileMode::Read);
        $stream->write('test');
    }

    #[Test]
    public function readFromWriteOnlyStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        $stream = new FileStream($this->temp_file, FileMode::WriteCreateOrTruncateExisting);
        $stream->read(10);
    }

    #[Test]
    public function getContentsOnWriteOnlyStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not readable');

        $stream = new FileStream($this->temp_file, FileMode::WriteCreateOrTruncateExisting);
        $stream->getContents();
    }

    #[Test]
    public function seekOnNonSeekableStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable');

        $stream = new FileStream($this->temp_file, FileMode::Read);
        $stream->detach();
        $stream->seek(0);
    }

    #[Test]
    public function tellOnDetachedStream(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new FileStream($this->temp_file, FileMode::Read);
        $stream->detach();
        $stream->tell();
    }
}
