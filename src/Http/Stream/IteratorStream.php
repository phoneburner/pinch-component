<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Stream;

use PhoneBurner\Pinch\Component\Http\Stream\Iterator\StreamIterator;
use Psr\Http\Message\StreamInterface;

/**
 * A StreamInterface implementation that reads from an array or Traversable
 * instance to produce the stream data. The instance is also an iterator itself. Note
 * that `getIterator()` returns a StreamIterator, not the underlying iterable.
 *
 * Unlike a typical PHP "foreach" operation, which calls next() immediately *after*
 * current() to move the iterator ahead, read operations with this class increment
 * the iterator with rewind()/next() first. This is intended to avoid blocking behavior
 * in next() from affecting returning the bytes retrieved from current().
 *
 * @implements \IteratorAggregate<string>
 */
class IteratorStream implements StreamInterface, \Stringable, \IteratorAggregate
{
    public const int CHUNK_BYTES = 8192;

    /**
     * @var \Iterator<string>
     */
    private readonly \Iterator $iterator;

    private int $counter = 0;

    private int|null $position = null;

    private string $buffer = '';

    /**
     * @param iterable<mixed, string> $iterable
     */
    public function __construct(iterable $iterable)
    {
        $this->iterator = match (true) {
            $iterable instanceof \Iterator => $iterable,
            \is_array($iterable) => new \ArrayIterator($iterable),
            default => new \IteratorIterator($iterable),
        };
    }

    #[\Override]
    public function __toString(): string
    {
        $this->rewind();
        return $this->getContents();
    }

    #[\Override]
    public function close(): void
    {
        // noop
    }

    #[\Override]
    public function detach(): null
    {
        return null;
    }

    #[\Override]
    public function getSize(): null
    {
        return null;
    }

    #[\Override]
    public function tell(): int
    {
        return (int)$this->position;
    }

    /**
     * @phpstan-impure
     */
    #[\Override]
    public function eof(): bool
    {
        return $this->position !== null
            && $this->buffer === ''
            && ! $this->iterator->valid();
    }

    #[\Override]
    public function isSeekable(): false
    {
        return false;
    }

    /**
     * @throws \RuntimeException Required by the StreamInterface spec, even though
     * this would be a better fit for a \LogicException
     */
    #[\Override]
    public function seek(int $offset, int $whence = \SEEK_SET): never
    {
        throw new \RuntimeException('Cannot seek an iterator stream');
    }

    #[\Override]
    public function rewind(): void
    {
        $this->iterator->rewind();
        $this->buffer = '';
        $this->position = 0;
        $this->counter = 0;
    }

    #[\Override]
    public function isWritable(): false
    {
        return false;
    }

    /**
     * @throws \RuntimeException Required by the StreamInterface spec, even though
     * this would be a better fit for a \LogicException
     */
    #[\Override]
    public function write(string $string): never
    {
        throw new \RuntimeException('Cannot write to an iterator stream');
    }

    #[\Override]
    public function isReadable(): true
    {
        return true;
    }

    #[\Override]
    public function read(int $length = self::CHUNK_BYTES): string
    {
        // If a previous call to `read()` retrieved more bytes from the iterator
        // than the requested length, do not move the iterator forward until the
        // buffer is drained.
        if ($this->buffer !== '') {
            $bytes = \substr($this->buffer, 0, $length);
            $this->position += \strlen($bytes);
            $this->buffer = \substr($this->buffer, $length);
            return $bytes;
        }

        // IteratorIterator instances must be rewound before use, otherwise the
        // current value of the inner Traversable will not be returned when the
        // IteratorIterator::current() method is called, but we want to do this
        // as late as possible, since it will have side effects, caching the
        // current value of the inner Traversable.
        if ($this->position === null) {
            $this->rewind();
        }

        if ($this->counter !== 0) {
            $this->iterator->next();
        }

        $current = $this->iterator->current();
        $this->buffer .= \is_scalar($current) || $current === null ? (string)$current : throw new \RuntimeException(
            'IteratorStream can only read from an iterable of strings',
        );

        ++$this->counter;
        $bytes = \substr($this->buffer, 0, $length);
        $this->position += \strlen($bytes);
        $this->buffer = \substr($this->buffer, $length);

        return $bytes;
    }

    #[\Override]
    public function getContents(): string
    {
        $contents = '';
        while (! $this->eof()) {
            $contents .= $this->read();
        }

        return $contents;
    }

    /**
     * @return array<string, mixed>|null
     */
    #[\Override]
    public function getMetadata(string|null $key = null): array|null
    {
        return $key === null ? [] : null;
    }

    public function getIterator(): StreamIterator
    {
        return new StreamIterator($this);
    }
}
