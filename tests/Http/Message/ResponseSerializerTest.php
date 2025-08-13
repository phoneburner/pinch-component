<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Message;

use GuzzleHttp\Psr7\Response;
use PhoneBurner\Pinch\Component\Http\Message\ResponseSerializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(ResponseSerializer::class)]
final class ResponseSerializerTest extends TestCase
{
    private ResponseSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ResponseSerializer();
    }

    #[Test]
    public function serializeConvertsResponseToString(): void
    {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json', 'Cache-Control' => 'no-cache'],
            '{"id":123,"name":"John Doe"}',
        );

        $result = $this->serializer->serialize($response);

        self::assertIsString($result);
        self::assertStringContainsString('HTTP/1.1 200 OK', $result);
        self::assertStringContainsString('Content-Type: application/json', $result);
        self::assertStringContainsString('Cache-Control: no-cache', $result);
        self::assertStringContainsString('{"id":123,"name":"John Doe"}', $result);
    }

    #[Test]
    #[DataProvider('provideValidResponses')]
    public function serializeHandlesVariousResponseTypes(
        ResponseInterface $response,
        int $expected_status,
        string $expected_phrase,
    ): void {
        $result = $this->serializer->serialize($response);

        self::assertStringContainsString(\sprintf('HTTP/1.1 %d %s', $expected_status, $expected_phrase), $result);
    }

    #[Test]
    public function serializeThrowsExceptionForNonResponseMessage(): void
    {
        $request = $this->createMock(RequestInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message must be an instance of ResponseInterface');

        /** @phpstan-ignore argument.type */
        $this->serializer->serialize($request);
    }

    #[Test]
    public function deserializeConvertsStringToResponse(): void
    {
        $http_string = "HTTP/1.1 201 Created\r\nContent-Type: application/json\r\nLocation: /api/users/456\r\n\r\n{\"id\":456,\"created\":true}";

        $result = $this->serializer->deserialize($http_string);

        self::assertSame(201, $result->getStatusCode());
        self::assertSame('Created', $result->getReasonPhrase());
        self::assertSame('{"id":456,"created":true}', (string)$result->getBody());
        self::assertSame(['application/json'], $result->getHeader('Content-Type'));
        self::assertSame(['/api/users/456'], $result->getHeader('Location'));
    }

    #[Test]
    #[DataProvider('provideValidHttpStrings')]
    public function deserializeHandlesVariousHttpStrings(string $http_string, array $expected_values): void
    {
        $result = $this->serializer->deserialize($http_string);

        self::assertSame($expected_values['status_code'], $result->getStatusCode());
        self::assertSame($expected_values['reason_phrase'], $result->getReasonPhrase());
    }

    #[Test]
    public function serializeAndDeserializeRoundTrip(): void
    {
        $original_response = new Response(
            404,
            ['Content-Type' => 'application/problem+json', 'X-Request-ID' => 'req-123'],
            '{"type":"not-found","title":"Resource Not Found"}',
        );

        $serialized = $this->serializer->serialize($original_response);
        $deserialized = $this->serializer->deserialize($serialized);

        self::assertSame($original_response->getStatusCode(), $deserialized->getStatusCode());
        self::assertSame($original_response->getReasonPhrase(), $deserialized->getReasonPhrase());
        self::assertSame((string)$original_response->getBody(), (string)$deserialized->getBody());
    }

    #[Test]
    public function serializeHandlesEmptyBody(): void
    {
        $response = new Response(204);

        $result = $this->serializer->serialize($response);

        self::assertStringContainsString('HTTP/1.1 204 No Content', $result);
        self::assertStringEndsWith("\r\n\r\n", $result);
    }

    #[Test]
    public function serializeHandlesLargeBody(): void
    {
        $large_body = \str_repeat('{"data":"test"}', 1000);
        $response = new Response(200, ['Content-Type' => 'application/json'], $large_body);

        $result = $this->serializer->serialize($response);

        self::assertStringContainsString('HTTP/1.1 200 OK', $result);
        self::assertStringContainsString($large_body, $result);
    }

    #[Test]
    public function serializeHandlesSpecialCharactersInBody(): void
    {
        $body_with_special_chars = '{"message":"Héllo 🌍 with émojis and spéciàl chars"}';
        $response = new Response(200, [], $body_with_special_chars);

        $result = $this->serializer->serialize($response);

        self::assertStringContainsString($body_with_special_chars, $result);
    }

    #[Test]
    public function serializeHandlesMultipleHeaderValues(): void
    {
        $response = new Response(
            200,
            [
                'Set-Cookie' => ['session=abc123; Path=/', 'csrf=xyz789; HttpOnly'],
                'Cache-Control' => 'public, max-age=3600',
            ],
        );

        $result = $this->serializer->serialize($response);

        self::assertStringContainsString('Set-Cookie: session=abc123; Path=/', $result);
        self::assertStringContainsString('Set-Cookie: csrf=xyz789; HttpOnly', $result);
        self::assertStringContainsString('Cache-Control: public, max-age=3600', $result);
    }

    #[Test]
    public function deserializeHandlesResponseWithoutBody(): void
    {
        $http_string = "HTTP/1.1 304 Not Modified\r\nETag: \"1234567890\"\r\n\r\n";

        $result = $this->serializer->deserialize($http_string);

        self::assertSame(304, $result->getStatusCode());
        self::assertSame('Not Modified', $result->getReasonPhrase());
        self::assertSame('', (string)$result->getBody());
        self::assertSame(['"1234567890"'], $result->getHeader('ETag'));
    }

    #[Test]
    public function deserializeHandlesCustomStatusCodes(): void
    {
        $http_string = "HTTP/1.1 422 Unprocessable Entity\r\nContent-Type: application/problem+json\r\n\r\n{\"errors\":[\"validation failed\"]}";

        $result = $this->serializer->deserialize($http_string);

        self::assertSame(422, $result->getStatusCode());
        self::assertSame('Unprocessable Entity', $result->getReasonPhrase());
    }

    #[Test]
    public function deserializeHandlesRedirectResponse(): void
    {
        $http_string = "HTTP/1.1 302 Found\r\nLocation: https://example.com/new-location\r\n\r\n";

        $result = $this->serializer->deserialize($http_string);

        self::assertSame(302, $result->getStatusCode());
        self::assertSame('Found', $result->getReasonPhrase());
        self::assertSame(['https://example.com/new-location'], $result->getHeader('Location'));
    }

    public static function provideValidResponses(): \Generator
    {
        yield 'successful response' => [
            new Response(200, [], '{"success":true}'),
            200,
            'OK',
        ];

        yield 'created response' => [
            new Response(201, ['Location' => '/api/users/123'], '{"id":123}'),
            201,
            'Created',
        ];

        yield 'bad request response' => [
            new Response(400, [], '{"error":"Bad Request"}'),
            400,
            'Bad Request',
        ];

        yield 'not found response' => [
            new Response(404, [], '{"error":"Not Found"}'),
            404,
            'Not Found',
        ];

        yield 'server error response' => [
            new Response(500, [], '{"error":"Internal Server Error"}'),
            500,
            'Internal Server Error',
        ];
    }

    public static function provideValidHttpStrings(): \Generator
    {
        yield 'simple success' => [
            "HTTP/1.1 200 OK\r\nContent-Type: text/plain\r\n\r\nSuccess",
            ['status_code' => 200, 'reason_phrase' => 'OK'],
        ];

        yield 'not found' => [
            "HTTP/1.1 404 Not Found\r\nContent-Type: application/problem+json\r\n\r\n{\"type\":\"not-found\"}",
            ['status_code' => 404, 'reason_phrase' => 'Not Found'],
        ];

        yield 'no content' => [
            "HTTP/1.1 204 No Content\r\n\r\n",
            ['status_code' => 204, 'reason_phrase' => 'No Content'],
        ];

        yield 'server error' => [
            "HTTP/1.1 500 Internal Server Error\r\nContent-Type: application/json\r\n\r\n{\"error\":\"Something went wrong\"}",
            ['status_code' => 500, 'reason_phrase' => 'Internal Server Error'],
        ];
    }
}
