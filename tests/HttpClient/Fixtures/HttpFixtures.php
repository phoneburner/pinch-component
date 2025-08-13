<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures;

use PhoneBurner\Pinch\Time\Timer\ElapsedTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Factory for creating HTTP-related test fixtures
 */
final class HttpFixtures
{
    public static function createMockRequest(
        string $method = 'POST',
        string $uri = 'https://example.com/webhook',
        array $headers = ['Content-Type' => 'application/json'],
        string $body = '{"event":"user.created","user_id":123}',
    ): RequestInterface {
        $request = new class ($method, $uri, $headers, $body) implements RequestInterface {
            public function __construct(
                private readonly string $method,
                private readonly string $uri,
                private array $headers,
                private readonly string $body,
            ) {
            }

            public function getRequestTarget(): string
            {
                return '/webhook';
            }

            public function withRequestTarget(string $request_target): RequestInterface
            {
                return $this;
            }

            public function getMethod(): string
            {
                return $this->method;
            }

            public function withMethod(string $method): RequestInterface
            {
                return new self($method, $this->uri, $this->headers, $this->body);
            }

            public function getUri(): UriInterface
            {
                return new readonly class ($this->uri) implements UriInterface {
                    public function __construct(private string $uri)
                    {
                    }

                    public function getScheme(): string
                    {
                        return 'https';
                    }

                    public function getAuthority(): string
                    {
                        return 'example.com';
                    }

                    public function getUserInfo(): string
                    {
                        return '';
                    }

                    public function getHost(): string
                    {
                        return 'example.com';
                    }

                    public function getPort(): int|null
                    {
                        return null;
                    }

                    public function getPath(): string
                    {
                        return '/webhook';
                    }

                    public function getQuery(): string
                    {
                        return '';
                    }

                    public function getFragment(): string
                    {
                        return '';
                    }

                    public function withScheme(string $scheme): UriInterface
                    {
                        return $this;
                    }

                    public function withUserInfo(string $user, string|null $password = null): UriInterface
                    {
                        return $this;
                    }

                    public function withHost(string $host): UriInterface
                    {
                        return $this;
                    }

                    public function withPort(int|null $port): UriInterface
                    {
                        return $this;
                    }

                    public function withPath(string $path): UriInterface
                    {
                        return $this;
                    }

                    public function withQuery(string $query): UriInterface
                    {
                        return $this;
                    }

                    public function withFragment(string $fragment): UriInterface
                    {
                        return $this;
                    }

                    public function __toString(): string
                    {
                        return $this->uri;
                    }
                };
            }

            public function withUri(UriInterface $uri, bool $preserve_host = false): RequestInterface
            {
                return $this;
            }

            public function getProtocolVersion(): string
            {
                return '1.1';
            }

            public function withProtocolVersion(string $version): RequestInterface
            {
                return $this;
            }

            public function getHeaders(): array
            {
                return $this->headers;
            }

            public function hasHeader(string $name): bool
            {
                return isset($this->headers[$name]);
            }

            public function getHeader(string $name): array
            {
                return isset($this->headers[$name]) ? [$this->headers[$name]] : [];
            }

            public function getHeaderLine(string $name): string
            {
                return $this->headers[$name] ?? '';
            }

            public function withHeader(string $name, $value): RequestInterface
            {
                $headers = $this->headers;
                $headers[$name] = $value;
                return new self($this->method, $this->uri, $headers, $this->body);
            }

            public function withAddedHeader(string $name, $value): RequestInterface
            {
                return $this->withHeader($name, $value);
            }

            public function withoutHeader(string $name): RequestInterface
            {
                $headers = $this->headers;
                unset($headers[$name]);
                return new self($this->method, $this->uri, $headers, $this->body);
            }

            public function getBody(): StreamInterface
            {
                return new readonly class ($this->body) implements StreamInterface {
                    public function __construct(private string $content)
                    {
                    }

                    public function __toString(): string
                    {
                        return $this->content;
                    }

                    public function close(): void
                    {
                    }

                    public function detach()
                    {
                        return null;
                    }

                    public function getSize(): int
                    {
                        return \strlen($this->content);
                    }

                    public function tell(): int
                    {
                        return 0;
                    }

                    public function eof(): bool
                    {
                        return true;
                    }

                    public function isSeekable(): bool
                    {
                        return false;
                    }

                    public function seek(int $offset, int $whence = \SEEK_SET): void
                    {
                    }

                    public function rewind(): void
                    {
                    }

                    public function isWritable(): bool
                    {
                        return false;
                    }

                    public function write(string $string): int
                    {
                        return 0;
                    }

                    public function isReadable(): bool
                    {
                        return true;
                    }

                    public function read(int $length): string
                    {
                        return $this->content;
                    }

                    public function getContents(): string
                    {
                        return $this->content;
                    }

                    public function getMetadata(string|null $key = null)
                    {
                        return null;
                    }
                };
            }

            public function withBody(StreamInterface $body): RequestInterface
            {
                return $this;
            }
        };

        return new $request($method, $uri, $headers, $body);
    }

    public static function createMockResponse(
        int $status_code = 200,
        string $reason_phrase = 'OK',
        array $headers = ['Content-Type' => 'application/json'],
        string $body = '{"success":true}',
    ): ResponseInterface {
        return new class ($status_code, $reason_phrase, $headers, $body) implements ResponseInterface {
            public function __construct(
                private readonly int $status_code,
                private readonly string $reason_phrase,
                private array $headers,
                private readonly string $body,
            ) {
            }

            public function getStatusCode(): int
            {
                return $this->status_code;
            }

            public function withStatus(int $code, string $reason_phrase = ''): ResponseInterface
            {
                return new self($code, $reason_phrase, $this->headers, $this->body);
            }

            public function getReasonPhrase(): string
            {
                return $this->reason_phrase;
            }

            public function getProtocolVersion(): string
            {
                return '1.1';
            }

            public function withProtocolVersion(string $version): ResponseInterface
            {
                return $this;
            }

            public function getHeaders(): array
            {
                return $this->headers;
            }

            public function hasHeader(string $name): bool
            {
                return isset($this->headers[$name]);
            }

            public function getHeader(string $name): array
            {
                return isset($this->headers[$name]) ? [$this->headers[$name]] : [];
            }

            public function getHeaderLine(string $name): string
            {
                return $this->headers[$name] ?? '';
            }

            public function withHeader(string $name, $value): ResponseInterface
            {
                $headers = $this->headers;
                $headers[$name] = $value;
                return new self($this->status_code, $this->reason_phrase, $headers, $this->body);
            }

            public function withAddedHeader(string $name, $value): ResponseInterface
            {
                return $this->withHeader($name, $value);
            }

            public function withoutHeader(string $name): ResponseInterface
            {
                $headers = $this->headers;
                unset($headers[$name]);
                return new self($this->status_code, $this->reason_phrase, $headers, $this->body);
            }

            public function getBody(): StreamInterface
            {
                return new readonly class ($this->body) implements StreamInterface {
                    public function __construct(private string $content)
                    {
                    }

                    public function __toString(): string
                    {
                        return $this->content;
                    }

                    public function close(): void
                    {
                    }

                    public function detach()
                    {
                        return null;
                    }

                    public function getSize(): int
                    {
                        return \strlen($this->content);
                    }

                    public function tell(): int
                    {
                        return 0;
                    }

                    public function eof(): bool
                    {
                        return true;
                    }

                    public function isSeekable(): bool
                    {
                        return false;
                    }

                    public function seek(int $offset, int $whence = \SEEK_SET): void
                    {
                    }

                    public function rewind(): void
                    {
                    }

                    public function isWritable(): bool
                    {
                        return false;
                    }

                    public function write(string $string): int
                    {
                        return 0;
                    }

                    public function isReadable(): bool
                    {
                        return true;
                    }

                    public function read(int $length): string
                    {
                        return $this->content;
                    }

                    public function getContents(): string
                    {
                        return $this->content;
                    }

                    public function getMetadata(string|null $key = null)
                    {
                        return null;
                    }
                };
            }

            public function withBody(StreamInterface $body): ResponseInterface
            {
                return $this;
            }
        };
    }

    public static function createElapsedTime(int $nanoseconds = 50000000): ElapsedTime
    {
        return new ElapsedTime($nanoseconds);
    }

    public static function createException(string $message = 'Connection timeout'): \RuntimeException
    {
        return new \RuntimeException($message);
    }
}
