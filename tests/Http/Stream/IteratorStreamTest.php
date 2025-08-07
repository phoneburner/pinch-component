<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Stream;

use PhoneBurner\Pinch\Component\Http\Stream\IteratorStream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IteratorStreamTest extends TestCase
{
    private const array METADATA_KEYS = [
        'wrapper_type',
        'stream_type',
        'mode',
        'unread_bytes',
        'seekable',
        'uri',
        'timed_out',
        'blocked',
        'eof',
    ];

    #[DataProvider('providesIterables')]
    #[Test]
    public function iteratorStreamHasExpectedStreamBehavior(
        iterable $iterable,
        string $expected,
    ): void {
        $stream = new IteratorStream($iterable);

        self::assertFalse($stream->isSeekable());
        self::assertFalse($stream->isWritable());
        self::assertTrue($stream->isReadable());
        self::assertNull($stream->getSize());
        self::assertSame([], $stream->getMetadata());
        foreach (self::METADATA_KEYS as $key) {
            self::assertNull($stream->getMetadata($key));
        }

        self::assertFalse($stream->eof());
        self::assertSame(0, $stream->tell());

        $contents = '';
        while (! $stream->eof()) {
            $contents .= $stream->read(10);
        }

        self::assertSame($expected, $contents);
        self::assertSame(\strlen($expected), $stream->tell());
        self::assertTrue($stream->eof());

        // detach should be a no-op
        self::assertNull($stream->detach());
    }

    #[Test]
    public function rewindResetsPosition(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('foobarbaz', (string)$stream);
        self::assertSame(9, $stream->tell());
        self::assertTrue($stream->eof());

        $stream->rewind();
        self::assertSame(0, $stream->tell());
        self::assertFalse($stream->eof());
        self::assertSame('foo', $stream->read());
        self::assertSame('bar', $stream->read());
        self::assertSame('baz', $stream->read());
    }

    #[Test]
    public function readRespectsLengthParam(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('fo', $stream->read(2));
        self::assertSame('o', $stream->read(2));
        self::assertSame('b', $stream->read(1));
        self::assertSame('a', $stream->read(1));
        self::assertSame('r', $stream->read(1));
        self::assertSame('baz', $stream->getContents());
    }

    #[Test]
    public function readRespectsLengthParam2(): void
    {
        $stream = new IteratorStream((fn(): \Generator => yield from ['foo', 'bar', 'baz'])());

        self::assertSame('foo', $stream->read(1000));
        self::assertSame('b', $stream->read(1));
        self::assertSame('ar', $stream->read(2));
        self::assertSame('baz', $stream->read(10));
    }

    #[Test]
    public function writeThrowsException(): never
    {
        $iterator = new \ArrayIterator(['foo', 'bar']);
        $stream = new IteratorStream($iterator);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot write to an iterator stream');
        $stream->write('baz');
    }

    #[Test]
    public function seekThrowsException(): never
    {
        $iterator = new \ArrayIterator(['foo', 'bar']);
        $stream = new IteratorStream($iterator);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot seek an iterator stream');
        $stream->seek(0);
    }

    #[Test]
    public function getContentsReturnsRemainingContentsString(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('foo', $stream->read(3));
        self::assertSame(3, $stream->tell());
        self::assertFalse($stream->eof());
        self::assertSame('barbaz', $stream->getContents());
        self::assertSame(9, $stream->tell());
        self::assertTrue($stream->eof());
    }

    #[DataProvider('providesIterables')]
    #[Test]
    public function getContentsHappyPath(iterable $iterable, string $expected): void
    {
        $stream = new IteratorStream($iterable);

        self::assertSame($expected, $stream->getContents());
        self::assertSame(\strlen($expected), $stream->tell());
        self::assertTrue($stream->eof());
    }

    #[Test]
    public function toStringRewindsAndReturnsContents(): void
    {
        $iterator = new \ArrayIterator(['foo', 'bar', 'baz']);
        $stream = new IteratorStream($iterator);

        self::assertSame('foo', $stream->read(3));
        self::assertSame(3, $stream->tell());
        self::assertFalse($stream->eof());
        self::assertSame('foobarbaz', (string)$stream);
        self::assertSame(9, $stream->tell());
        self::assertTrue($stream->eof());
    }

    #[DataProvider('providesIterables')]
    #[Test]
    public function toStringHappyPath(iterable $iterable, string $expected): void
    {
        $stream = new IteratorStream($iterable);

        self::assertSame($expected, (string)$stream);
        self::assertSame(\strlen($expected), $stream->tell());
        self::assertTrue($stream->eof());
    }

    public static function providesIterables(): \Generator
    {
        $expected = '';
        $values = [];
        while (\strlen($expected) < 8192 * 100) {
            $value = \random_bytes(\random_int(8192, 8192 * 10)) . \PHP_EOL;
            $values[] = $value;
            $expected .= $value;
        }

        $single_value = \random_bytes(8192 * 3);

        $tests = [
            'Empty' => ['values' => [], 'expected' => ''],
            'Short' => ['values' => ['foo', 'bar', 'baz'], 'expected' => 'foobarbaz'],
            'SingleValue' => ['values' => [$single_value], 'expected' => $single_value],
            'Big' => ['values' => $values, 'expected' => $expected],
        ];

        foreach ($tests as $name => ['values' => $values, 'expected' => $expected]) {
            yield $name . 'Array' => [$values, $expected];

            yield $name . 'Iterator' => [new \ArrayIterator($values), $expected];

            yield $name . 'IteratorIterator' => [new \IteratorIterator(new \ArrayIterator($values)), $expected];

            yield $name . 'Generator' => [(static function () use ($values) {
                yield from $values;
            })(), $expected];

            yield $name . 'IteratorAggregate' => [new readonly class ($values) implements \IteratorAggregate {
                public function __construct(private array $values)
                {
                }

                public function getIterator(): \Traversable
                {
                    return yield from $this->values;
                }
            }, $expected];
        }
    }
}
