<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Stream;

use PhoneBurner\Pinch\Filesystem\File;
use PhoneBurner\Pinch\Filesystem\FileMode;
use Psr\Http\Message\StreamInterface;

use function PhoneBurner\Pinch\Type\narrow_positive_int;

final class FileStream implements StreamInterface, \Stringable
{
    public const int CHUNK_BYTES = 8192;

    /**
     * @var resource|null
     */
    private mixed $resource;

    private bool $readable;

    private bool $writable;

    private bool $seekable;

    public function __construct(\Stringable|string $path, public readonly FileMode $mode)
    {
        $this->resource = File::open($path, $mode);
        $this->readable = $mode->isReadable();
        $this->writable = $mode->isWritable();
        $this->seekable = \stream_get_meta_data($this->resource)['seekable'];
    }

    public function __toString(): string
    {
        if (! $this->isReadable()) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (\RuntimeException) {
            return '';
        }
    }

    public function close(): void
    {
        $resource = $this->detach();
        if ($resource) {
            \fclose($resource);
        }
    }

    public function detach(): mixed
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->readable = false;
        $this->writable = false;
        $this->seekable = false;
        return $resource;
    }

    public function getSize(): int|null
    {
        if ($this->resource === null) {
            return null;
        }

        return (\fstat($this->resource) ?: [])['size'] ?? null;
    }

    public function tell(): int
    {
        $tell = \ftell($this->stream());
        if ($tell === false) {
            throw new \RuntimeException('Unable to tell position of stream');
        }

        return $tell;
    }

    public function eof(): bool
    {
        return $this->resource === null || \feof($this->resource);
    }

    /**
     * @phpstan-assert-if-true !null $this->resource
     */
    public function isSeekable(): bool
    {
        return $this->resource && $this->seekable;
    }

    public function seek(int $offset, int $whence = \SEEK_SET): void
    {
        if (! $this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        \fseek($this->stream(), $offset, $whence);
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @phpstan-assert-if-true !null $this->resource
     */
    public function isWritable(): bool
    {
        return $this->resource && $this->writable;
    }

    public function write(string $string): int
    {
        if (! $this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }

        $bytes = \fwrite($this->stream(), $string);
        if ($bytes === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        return $bytes;
    }

    /**
     * @phpstan-assert-if-true !null $this->resource
     */
    public function isReadable(): bool
    {
        return $this->resource && $this->readable;
    }

    public function read(int $length = self::CHUNK_BYTES): string
    {
        if (! $this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $bytes = \fread($this->resource, narrow_positive_int($length));
        if ($bytes === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $bytes;
    }

    public function getContents(): string
    {
        if (! $this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }

        $contents = \stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $contents;
    }

    public function getMetadata(string|null $key = null): mixed
    {
        if ($key === null) {
            return \stream_get_meta_data($this->stream());
        }

        return \stream_get_meta_data($this->stream())[$key] ?? null;
    }

    /**
     * @return resource
     */
    private function stream(): mixed
    {
        return $this->resource ?? throw new \RuntimeException('Stream resource detached from instance');
    }
}
