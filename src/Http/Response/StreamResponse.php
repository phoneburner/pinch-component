<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response;

use Laminas\Diactoros\Response;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Psr7;
use Psr\Http\Message\StreamInterface;

class StreamResponse extends Response
{
    /**
     * Creates a stream response from a string body or stream, in contrast to the
     * constructor which accepts a stream, stream identifier string, or resource.
     *
     * @param array<string, string|array<string>> $headers
     */
    public static function make(string|StreamInterface $stream, int $status = HttpStatus::OK, array $headers = []): self
    {
        return new self(Psr7::stream($stream), $status, $headers);
    }
}
