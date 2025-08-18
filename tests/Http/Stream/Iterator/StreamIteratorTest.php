<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Stream\Iterator;

use PhoneBurner\Pinch\Component\Http\Stream\Iterator\StreamIterator;
use PhoneBurner\Pinch\Component\Http\Stream\MemoryStream;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StreamIteratorTest extends TestCase
{
    #[Test]
    public function happyPathSeekable(): void
    {
        $content = \random_bytes(8192 * 10);

        $stream = new MemoryStream();
        $stream->write($content);

        $iterator = new StreamIterator($stream, 1000);

        $bytes = '';
        $total_length = 0;
        foreach ($iterator as $chunk) {
            $chunk_length = \strlen($chunk);
            $total_length += $chunk_length;
            $bytes .= $chunk;

            self::assertSame($total_length, $iterator->key());
            self::assertLessThanOrEqual(1000, $chunk_length);
        }

        self::assertSame($content, $bytes);

        $stream->rewind();

        $bytes = '';
        $total_length = 0;
        foreach ($iterator as $chunk) {
            $chunk_length = \strlen($chunk);
            $total_length += $chunk_length;
            $bytes .= $chunk;

            self::assertSame($total_length, $iterator->key());
            self::assertLessThanOrEqual(1000, $chunk_length);
        }

        self::assertSame($content, $bytes);
    }

    #[Test]
    public function happyPathNonseekable(): void
    {
        $content = \random_bytes(8192 * 10);

        $stream = new class extends MemoryStream {
            protected bool $seekable = false;
        };
        $stream->write($content);
        $stream->rewind();

        $iterator = new StreamIterator($stream, 1000);

        $bytes = '';
        $total_length = 0;
        foreach ($iterator as $chunk) {
            $chunk_length = \strlen($chunk);
            $total_length += $chunk_length;
            $bytes .= $chunk;

            self::assertSame($total_length, $iterator->key());
            self::assertLessThanOrEqual(1000, $chunk_length);
        }

        self::assertSame($content, $bytes);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot Rewind Non-Seekable Stream and Stream at EOF');
        foreach ($iterator as $chunk) {
            // noop
        }
    }
}
