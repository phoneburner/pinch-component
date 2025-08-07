<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Laminas\Diactoros\Stream;
use PhoneBurner\Pinch\Component\Http\Stream\FileStream;
use PhoneBurner\Pinch\Component\Http\Stream\InMemoryStream;
use PhoneBurner\Pinch\Filesystem\File;
use PhoneBurner\Pinch\Filesystem\FileMode;
use Psr\Http\Message\StreamFactoryInterface;

class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): InMemoryStream
    {
        return new InMemoryStream($content);
    }

    public function createStreamFromFile(
        \Stringable|string $filename,
        FileMode|string $mode = FileMode::Read,
    ): FileStream {
        return new FileStream(File::filename($filename), FileMode::instance($mode));
    }

    public function createStreamFromResource($resource): Stream
    {
        if (! \is_resource($resource)) {
            throw new \InvalidArgumentException('Resource must be a valid resource');
        }

        return new Stream($resource);
    }

    public static function memory(string $content = ''): InMemoryStream|null
    {
        try {
            return new self()->createStream($content);
        } catch (\Exception) {
            return null;
        }
    }

    public static function file(\Stringable|string $filename, FileMode|string $mode = FileMode::Read): FileStream|null
    {
        try {
            return new self()->createStreamFromFile($filename, $mode);
        } catch (\Exception) {
            return null;
        }
    }
}
