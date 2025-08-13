<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Stream;

use PhoneBurner\Pinch\Component\Http\Stream\TemporaryStream;
use PhoneBurner\Pinch\Memory\Bytes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class TemporaryStreamTest extends TestCase
{
    private array $temp_files = [];

    protected function tearDown(): void
    {
        // Clean up any temporary files that may have been created during testing
        foreach ($this->temp_files as $file) {
            if (\file_exists($file)) {
                \unlink($file);
            }
        }
        $this->temp_files = [];
    }

    #[Test]
    public function constructorCreatesEmptyStreamByDefault(): void
    {
        $stream = new TemporaryStream();

        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isSeekable());
        self::assertSame(0, $stream->getSize());
        self::assertSame(0, $stream->tell());
        self::assertSame('', (string)$stream);
    }

    #[Test]
    public function constructorAcceptsInitialContent(): void
    {
        $content = 'initial content';
        $stream = new TemporaryStream($content);

        self::assertSame(\strlen($content), $stream->getSize());
        self::assertSame(0, $stream->tell());
        self::assertSame($content, (string)$stream);
    }

    #[Test]
    public function constructorAcceptsCustomMaxMemory(): void
    {
        $max_memory = new Bytes(1024);
        $stream = new TemporaryStream('test', $max_memory);

        self::assertSame(4, $stream->getSize());
        self::assertSame('test', (string)$stream);
    }

    #[Test]
    public function makeFactoryMethodWorksAsExpected(): void
    {
        $content = 'factory content';
        $stream = TemporaryStream::make($content);

        self::assertInstanceOf(TemporaryStream::class, $stream);
        self::assertSame($content, (string)$stream);
    }

    #[Test]
    public function makeFactoryMethodWithCustomMaxMemory(): void
    {
        $max_memory = new Bytes(512);
        $stream = TemporaryStream::make('test', $max_memory);

        self::assertInstanceOf(TemporaryStream::class, $stream);
        self::assertSame('test', (string)$stream);
    }

    #[Test]
    public function copyFromAnotherStreamWithRewind(): void
    {
        $original = TemporaryStream::make('original content');
        $original->seek(5); // Move position away from start

        $copy = TemporaryStream::copy($original, true);

        self::assertSame('original content', (string)$copy);
        self::assertSame(0, $original->tell()); // Original should be rewound
        self::assertSame(16, $copy->tell()); // Copy position is at end after toString
    }

    #[Test]
    public function copyFromAnotherStreamWithoutRewind(): void
    {
        $original = TemporaryStream::make('original content');
        $original->seek(9); // Position at 'content'

        $copy = TemporaryStream::copy($original, false);

        self::assertSame('content', (string)$copy);
        self::assertTrue($original->eof()); // Original position at end
    }

    #[Test]
    public function copyFromNonSeekableStream(): void
    {
        $mock_stream = $this->createMock(StreamInterface::class);
        $mock_stream->method('isSeekable')->willReturn(false);
        $mock_stream->method('eof')->willReturnOnConsecutiveCalls(false, false, true);
        $mock_stream->method('read')->willReturnOnConsecutiveCalls('hello', ' world', '');

        $copy = TemporaryStream::copy($mock_stream);

        self::assertSame('hello world', (string)$copy);
    }

    #[Test]
    public function writeIncreasesSize(): void
    {
        $stream = new TemporaryStream();

        $bytes_written = $stream->write('hello');
        self::assertSame(5, $bytes_written);
        self::assertSame(5, $stream->getSize());
        self::assertSame(5, $stream->tell());

        $bytes_written = $stream->write(' world');
        self::assertSame(6, $bytes_written);
        self::assertSame(11, $stream->getSize());
        self::assertSame(11, $stream->tell());
    }

    #[Test]
    public function readFromStream(): void
    {
        $content = 'hello world';
        $stream = new TemporaryStream($content);

        $chunk = $stream->read(5);
        self::assertSame('hello', $chunk);
        self::assertSame(5, $stream->tell());

        $chunk = $stream->read(6);
        self::assertSame(' world', $chunk);
        self::assertSame(11, $stream->tell());
    }

    #[Test]
    public function readWithDefaultChunkSize(): void
    {
        $content = \str_repeat('a', TemporaryStream::CHUNK_BYTES + 100);
        $stream = new TemporaryStream($content);

        $chunk = $stream->read();
        self::assertSame(TemporaryStream::CHUNK_BYTES, \strlen($chunk));
        self::assertSame(TemporaryStream::CHUNK_BYTES, $stream->tell());
    }

    #[Test]
    public function readWithZeroLengthReturnsMinimumOneByte(): void
    {
        $stream = new TemporaryStream('abc');

        $chunk = $stream->read(0);
        self::assertSame('a', $chunk);
        self::assertSame(1, $stream->tell());
    }

    #[Test]
    public function getContentsReturnsRemainingContent(): void
    {
        $content = 'hello world';
        $stream = new TemporaryStream($content);
        $stream->seek(6);

        $remaining = $stream->getContents();
        self::assertSame('world', $remaining);
    }

    #[Test]
    public function seekAndTellOperations(): void
    {
        $stream = new TemporaryStream('hello world');

        self::assertSame(0, $stream->tell());

        $stream->seek(6);
        self::assertSame(6, $stream->tell());

        $stream->seek(2, \SEEK_CUR);
        self::assertSame(8, $stream->tell());

        $stream->seek(-3, \SEEK_END);
        self::assertSame(8, $stream->tell());
    }

    #[Test]
    public function rewindResetsPosition(): void
    {
        $stream = new TemporaryStream('hello world');
        $stream->seek(5);

        self::assertSame(5, $stream->tell());

        $stream->rewind();
        self::assertSame(0, $stream->tell());
    }

    #[Test]
    public function eofDetection(): void
    {
        $stream = new TemporaryStream('hello');

        self::assertFalse($stream->eof());

        $stream->getContents();
        self::assertTrue($stream->eof());

        $stream->rewind();
        self::assertFalse($stream->eof());
    }

    #[Test]
    public function streamStatesAfterConstruction(): void
    {
        $stream = new TemporaryStream();

        self::assertTrue($stream->isReadable());
        self::assertTrue($stream->isWritable());
        self::assertTrue($stream->isSeekable());
    }

    #[Test]
    public function detachChangesStreamStates(): void
    {
        $stream = new TemporaryStream('test');
        $resource = $stream->detach();

        self::assertIsResource($resource);
        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isSeekable());

        // Clean up the resource
        \fclose($resource);
    }

    #[Test]
    public function detachReturnsNullWhenCalledTwice(): void
    {
        $stream = new TemporaryStream('test');
        $resource = $stream->detach();
        self::assertIsResource($resource);
        \fclose($resource);

        $second_detach = $stream->detach();
        self::assertNull($second_detach);
    }

    #[Test]
    public function closeReleasesResource(): void
    {
        $stream = new TemporaryStream('test');
        $stream->close();

        self::assertFalse($stream->isReadable());
        self::assertFalse($stream->isWritable());
        self::assertFalse($stream->isSeekable());
    }

    #[Test]
    public function destructorClosesStream(): void
    {
        $stream = new TemporaryStream('test');
        $reflection = new \ReflectionProperty($stream, 'stream');
        $resource = $reflection->getValue($stream);

        self::assertIsResource($resource);

        unset($stream); // Trigger destructor

        // Resource should be closed after destructor
        self::assertFalse(\is_resource($resource));
    }

    #[Test]
    public function getMetadataReturnsStreamInformation(): void
    {
        $stream = new TemporaryStream('test');
        $metadata = $stream->getMetadata();

        self::assertIsArray($metadata);
        self::assertArrayHasKey('wrapper_type', $metadata);
        self::assertArrayHasKey('stream_type', $metadata);
        self::assertArrayHasKey('mode', $metadata);
        self::assertArrayHasKey('unread_bytes', $metadata);
        self::assertArrayHasKey('seekable', $metadata);
        self::assertTrue($metadata['seekable']);
    }

    #[Test]
    public function getMetadataWithSpecificKey(): void
    {
        $stream = new TemporaryStream('test');

        self::assertTrue($stream->getMetadata('seekable'));
        self::assertNull($stream->getMetadata('non_existent_key'));
    }

    #[Test]
    public function toStringReturnsFullContent(): void
    {
        $content = 'hello world';
        $stream = new TemporaryStream($content);
        $stream->seek(5); // Move position

        $string_result = (string)$stream;
        self::assertSame($content, $string_result);
        self::assertSame(11, $stream->tell()); // Position is at end after getContents()
    }

    #[Test]
    public function smallContentStaysInMemory(): void
    {
        $small_content = 'small content';
        $stream = new TemporaryStream($small_content, new Bytes(1024));
        $metadata = $stream->getMetadata();

        // For php://temp, small content stays in memory
        self::assertSame('PHP', $metadata['wrapper_type']);
        self::assertSame($small_content, (string)$stream);
    }

    #[Test]
    public function largeContentMovesToTempFile(): void
    {
        // Create content larger than max memory limit
        $large_content = \str_repeat('x', 100);
        $stream = new TemporaryStream($large_content, new Bytes(50));

        self::assertSame(\strlen($large_content), $stream->getSize());
        self::assertSame($large_content, (string)$stream);
    }

    #[Test]
    public function memoryThresholdBehavior(): void
    {
        $max_memory = new Bytes(64);
        $stream = new TemporaryStream('', $max_memory);

        // Write content just under the threshold
        $small_content = \str_repeat('a', 32);
        $stream->write($small_content);
        self::assertSame(32, $stream->getSize());

        // Write content that exceeds the threshold
        $additional_content = \str_repeat('b', 64);
        $stream->write($additional_content);
        self::assertSame(96, $stream->getSize());

        $stream->rewind();
        $full_content = $stream->getContents();
        self::assertSame(96, \strlen($full_content));
        self::assertStringStartsWith($small_content, $full_content);
        self::assertStringEndsWith($additional_content, $full_content);
    }

    #[Test]
    #[DataProvider('provideInvalidBytesValues')]
    public function constructorWithInvalidMaxMemoryThrowsException(int $invalid_bytes): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Bytes must be non-negative integer');

        new TemporaryStream('test', new Bytes($invalid_bytes));
    }

    public static function provideInvalidBytesValues(): \Generator
    {
        yield 'negative bytes' => [-1];
        yield 'large negative bytes' => [-1000];
    }

    #[Test]
    public function tellThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->tell();
    }

    #[Test]
    public function writeThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->write('more content');
    }

    #[Test]
    public function readThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->read(5);
    }

    #[Test]
    public function getContentsThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->getContents();
    }

    #[Test]
    public function seekThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->seek(0);
    }

    #[Test]
    public function rewindThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->rewind();
    }

    #[Test]
    public function eofThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->eof();
    }

    #[Test]
    public function getSizeThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->getSize();
    }

    #[Test]
    public function getMetadataThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        $stream->getMetadata();
    }

    #[Test]
    public function toStringThrowsExceptionWhenResourceDetached(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream resource detached from instance');

        $stream = new TemporaryStream('test');
        $stream->detach();
        self::assertSame('', (string)$stream);
    }

    #[Test]
    #[DataProvider('provideEmptyStreamOperations')]
    public function emptyStreamOperations(string $operation, mixed $expected): void
    {
        $stream = new TemporaryStream();

        $result = match ($operation) {
            'getSize' => $stream->getSize(),
            'tell' => $stream->tell(),
            'eof' => $stream->eof(),
            'getContents' => $stream->getContents(),
            'toString' => (string)$stream,
            'read' => $stream->read(10),
            default => throw new \InvalidArgumentException('Unknown operation: ' . $operation),
        };

        self::assertSame($expected, $result);
    }

    public static function provideEmptyStreamOperations(): \Generator
    {
        yield 'getSize on empty stream' => ['getSize', 0];
        yield 'tell on empty stream' => ['tell', 0];
        yield 'eof on empty stream' => ['eof', false];
        yield 'getContents on empty stream' => ['getContents', ''];
        yield 'toString on empty stream' => ['toString', ''];
        yield 'read on empty stream' => ['read', ''];
    }

    #[Test]
    public function writeAndReadCyclePreservesData(): void
    {
        $stream = new TemporaryStream();
        $test_data = 'Hello, World! This is a test of the TemporaryStream implementation.';

        // Write data in chunks
        $chunks = \str_split($test_data, 10);
        foreach ($chunks as $chunk) {
            $stream->write($chunk);
        }

        // Read it back
        $stream->rewind();
        $read_data = $stream->getContents();

        self::assertSame($test_data, $read_data);
        self::assertSame(\strlen($test_data), $stream->getSize());
    }

    #[Test]
    public function chunkBasedReading(): void
    {
        $test_data = \str_repeat('abcdefghij', 100); // 1000 chars
        $stream = new TemporaryStream($test_data);

        $chunks = [];
        while (! $stream->eof()) {
            $chunk = $stream->read(TemporaryStream::CHUNK_BYTES);
            if ($chunk !== '') {
                $chunks[] = $chunk;
            }
        }

        $reconstructed = \implode('', $chunks);
        self::assertSame($test_data, $reconstructed);
    }

    #[Test]
    public function positionTrackingDuringOperations(): void
    {
        $stream = new TemporaryStream('0123456789');

        self::assertSame(0, $stream->tell());

        $stream->read(3);
        self::assertSame(3, $stream->tell());

        $stream->write('ABC');
        self::assertSame(6, $stream->tell());

        $stream->seek(0);
        self::assertSame(0, $stream->tell());

        $stream->seek(2, \SEEK_CUR);
        self::assertSame(2, $stream->tell());

        $stream->seek(-1, \SEEK_END);
        self::assertSame($stream->getSize() - 1, $stream->tell());
    }
}
