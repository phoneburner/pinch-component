<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Request;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Stream\TemporaryStream;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

interface RequestFactory extends RequestFactoryInterface, ServerRequestFactoryInterface
{
    /**
     * @param array<string, string|array<string>> $headers
     */
    public function createRequest(
        HttpMethod|string $method,
        mixed $uri,
        array $headers = [],
        StreamInterface $body = new TemporaryStream(),
    ): RequestInterface;

    /**
     * @param array<mixed> $serverParams
     * @param array<string, string|array<string>> $headers
     * @param array<string, mixed> $query
     * @param array<string, string> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed>|object|null $parsed
     * @param array<string, mixed> $attributes
     */
    public function createServerRequest(
        HttpMethod|string $method,
        mixed $uri,
        array $serverParams = [],
        StreamInterface $body = new TemporaryStream(),
        array $headers = [],
        array $query = [],
        array $cookies = [],
        array $files = [],
        array|object|null $parsed = null,
        string $protocol = '1.1',
        array $attributes = [],
    ): ServerRequestInterface;
}
