<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Message;

use GuzzleHttp\Psr7\Request;
use PhoneBurner\Pinch\Component\Http\Message\RequestSerializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(RequestSerializer::class)]
final class RequestSerializerTest extends TestCase
{
    private RequestSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new RequestSerializer();
    }

    #[Test]
    public function serializeConvertsRequestToString(): void
    {
        $request = new Request(
            'POST',
            '/api/users',
            ['Content-Type' => 'application/json'],
            '{"name":"John Doe"}',
        );

        $result = $this->serializer->serialize($request);

        self::assertIsString($result);
        self::assertStringContainsString('POST /api/users HTTP/1.1', $result);
        self::assertStringContainsString('Content-Type: application/json', $result);
        self::assertStringContainsString('{"name":"John Doe"}', $result);
    }

    #[Test]
    #[DataProvider('provideValidRequests')]
    public function serializeHandlesVariousRequestTypes(
        RequestInterface $request,
        string $expected_method,
        string $expected_path,
    ): void {
        $result = $this->serializer->serialize($request);

        self::assertStringContainsString($expected_method, $result);
        self::assertStringContainsString($expected_path, $result);
        self::assertStringContainsString('HTTP/1.1', $result);
    }

    #[Test]
    public function serializeThrowsExceptionForNonRequestMessage(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message must be an instance of RequestInterface');

        /** @phpstan-ignore argument.type */
        $this->serializer->serialize($response);
    }

    #[Test]
    public function deserializeConvertsStringToRequest(): void
    {
        $http_string = "GET /api/users HTTP/1.1\r\nHost: example.com\r\nAccept: application/json\r\n\r\n";

        $result = $this->serializer->deserialize($http_string);

        self::assertInstanceOf(RequestInterface::class, $result);
        self::assertSame('GET', $result->getMethod());
        self::assertSame('/api/users', $result->getRequestTarget());
        self::assertSame(['application/json'], $result->getHeader('Accept'));
    }

    #[Test]
    #[DataProvider('provideValidHttpStrings')]
    public function deserializeHandlesVariousHttpStrings(string $http_string, array $expected_values): void
    {
        $result = $this->serializer->deserialize($http_string);

        self::assertSame($expected_values['method'], $result->getMethod());
        self::assertSame($expected_values['path'], $result->getRequestTarget());
    }

    #[Test]
    public function serializeAndDeserializeRoundTrip(): void
    {
        $original_request = new Request(
            'PUT',
            '/api/users/456',
            ['Content-Type' => 'application/json', 'Accept' => 'application/hal+json'],
            '{"email":"updated@example.com"}',
        );

        $serialized = $this->serializer->serialize($original_request);
        $deserialized = $this->serializer->deserialize($serialized);

        self::assertSame($original_request->getMethod(), $deserialized->getMethod());
        self::assertSame($original_request->getRequestTarget(), $deserialized->getRequestTarget());
        self::assertSame((string)$original_request->getBody(), (string)$deserialized->getBody());
    }

    #[Test]
    public function serializeHandlesEmptyBody(): void
    {
        $request = new Request('DELETE', '/api/users/789');

        $result = $this->serializer->serialize($request);

        self::assertStringContainsString('DELETE /api/users/789 HTTP/1.1', $result);
    }

    #[Test]
    public function serializeHandlesLargeBody(): void
    {
        $large_body = \str_repeat('{"data":"test"}', 1000);
        $request = new Request('POST', '/api/bulk', ['Content-Type' => 'application/json'], $large_body);

        $result = $this->serializer->serialize($request);

        self::assertStringContainsString('POST /api/bulk HTTP/1.1', $result);
        self::assertStringContainsString($large_body, $result);
    }

    #[Test]
    public function serializeHandlesSpecialCharactersInBody(): void
    {
        $body_with_special_chars = '{"message":"Hello 🌍 with émojis and spéciàl chars"}';
        $request = new Request('POST', '/api/message', [], $body_with_special_chars);

        $result = $this->serializer->serialize($request);

        self::assertStringContainsString($body_with_special_chars, $result);
    }

    #[Test]
    public function deserializeHandlesRequestWithoutBody(): void
    {
        $http_string = "HEAD /api/status HTTP/1.1\r\nHost: api.example.com\r\n\r\n";

        $result = $this->serializer->deserialize($http_string);

        self::assertSame('HEAD', $result->getMethod());
        self::assertSame('/api/status', $result->getRequestTarget());
        self::assertSame('', (string)$result->getBody());
    }

    #[Test]
    public function deserializeHandlesRequestWithQueryParameters(): void
    {
        $http_string = "GET /api/search?q=test&limit=10 HTTP/1.1\r\nHost: example.com\r\n\r\n";

        $result = $this->serializer->deserialize($http_string);

        self::assertSame('GET', $result->getMethod());
        self::assertSame('/api/search?q=test&limit=10', $result->getRequestTarget());
    }

    public static function provideValidRequests(): \Generator
    {
        yield 'GET request' => [
            new Request('GET', '/api/status'),
            'GET',
            '/api/status',
        ];

        yield 'POST with JSON body' => [
            new Request('POST', '/api/create', ['Content-Type' => 'application/json'], '{"data":"test"}'),
            'POST',
            '/api/create',
        ];

        yield 'PUT with query params' => [
            new Request('PUT', '/api/update?version=1'),
            'PUT',
            '/api/update?version=1',
        ];

        yield 'PATCH request' => [
            new Request('PATCH', '/api/users/123'),
            'PATCH',
            '/api/users/123',
        ];
    }

    public static function provideValidHttpStrings(): \Generator
    {
        yield 'simple GET' => [
            "GET / HTTP/1.1\r\nHost: example.com\r\n\r\n",
            ['method' => 'GET', 'path' => '/'],
        ];

        yield 'POST with body' => [
            "POST /submit HTTP/1.1\r\nContent-Type: application/json\r\n\r\n{\"data\":\"value\"}",
            ['method' => 'POST', 'path' => '/submit'],
        ];

        yield 'GET with query string' => [
            "GET /search?q=term HTTP/1.1\r\nHost: api.example.com\r\n\r\n",
            ['method' => 'GET', 'path' => '/search?q=term'],
        ];
    }
}
