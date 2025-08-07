<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\IpAddress\IpAddress;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface, ServerRequestFactoryInterface
{
    public function fromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals()
            ->withAttribute(IpAddress::class, IpAddress::marshall($_SERVER));
    }

    /**
     * @param array<string, string|array<string>> $headers
     */
    public function createRequest(
        HttpMethod|string $method,
        mixed $uri,
        array $headers = [],
        StreamInterface $body = new Stream('php://temp', 'w+b'),
    ): Request {
        return new Request(
            $uri instanceof UriInterface ? $uri : new Uri($uri),
            HttpMethod::instance($method)->value,
            $body,
            $headers,
        );
    }

    /**
     * @param array<mixed> $serverParams
     */
    public function createServerRequest(
        HttpMethod|string $method,
        mixed $uri,
        array $serverParams = [],
    ): ServerRequestInterface {
        $uri = $uri instanceof UriInterface ? $uri : new Uri($uri);
        return $this->server($method, $uri, server: $serverParams);
    }

    /**
     * @param array<string, string|array<string>> $headers
     * @param array<string, mixed> $server
     * @param array<string, mixed> $query
     * @param array<string, string> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed>|object|null $parsed
     * @param array<string, mixed> $attributes
     */
    public function server(
        HttpMethod|string $method,
        UriInterface|string $uri,
        StreamInterface $body = new Stream('php://temp', 'w+b'),
        array $headers = [],
        array $server = [],
        array $query = [],
        array $cookies = [],
        array $files = [],
        array|object|null $parsed = null,
        string $protocol = '1.1',
        array $attributes = [],
    ): ServerRequestInterface {
        $request = new ServerRequest(
            $server,
            $files,
            $uri instanceof UriInterface ? $uri : new Uri($uri),
            HttpMethod::instance($method)->value,
            $body,
            $headers,
            $cookies,
            $query,
            $parsed,
            $protocol,
        );

        foreach ($attributes as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }
}
