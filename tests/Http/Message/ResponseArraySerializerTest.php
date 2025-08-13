<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Message;

use GuzzleHttp\Psr7\Response;
use PhoneBurner\Pinch\Component\Http\Message\ResponseArraySerializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(ResponseArraySerializer::class)]
final class ResponseArraySerializerTest extends TestCase
{
    private ResponseArraySerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ResponseArraySerializer();
    }

    #[Test]
    public function serializeConvertsResponseToArray(): void
    {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json', 'Cache-Control' => 'no-cache'],
            '{"id":123,"name":"John Doe"}',
        );

        $result = $this->serializer->serialize($response);

        self::assertIsArray($result);
        self::assertSame(200, $result['status_code']);
        self::assertSame('OK', $result['reason_phrase']);
        self::assertSame('1.1', $result['protocol_version']);
        self::assertArrayHasKey('headers', $result);
        self::assertSame('{"id":123,"name":"John Doe"}', $result['body']);
    }

    #[Test]
    #[DataProvider('provideValidResponses')]
    public function serializeHandlesVariousResponseTypes(ResponseInterface $response, array $expected_values): void
    {
        $result = $this->serializer->serialize($response);

        self::assertSame($expected_values['status_code'], $result['status_code']);
        self::assertSame($expected_values['reason_phrase'], $result['reason_phrase']);
        self::assertSame($expected_values['body'], $result['body']);
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
    public function deserializeConvertsArrayToResponse(): void
    {
        $array_data = [
            'status_code' => 201,
            'reason_phrase' => 'Created',
            'protocol_version' => '1.1',
            'headers' => [
                'content-type' => ['application/json'],
                'location' => ['/api/users/456'],
            ],
            'body' => '{"id":456,"created":true}',
        ];

        $result = $this->serializer->deserialize($array_data);

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(201, $result->getStatusCode());
        self::assertSame('Created', $result->getReasonPhrase());
        self::assertSame('{"id":456,"created":true}', (string)$result->getBody());
        self::assertSame(['application/json'], $result->getHeader('content-type'));
        self::assertSame(['/api/users/456'], $result->getHeader('location'));
    }

    #[Test]
    #[DataProvider('provideValidArrayData')]
    public function deserializeHandlesVariousArrayStructures(array $array_data, array $expected_values): void
    {
        $result = $this->serializer->deserialize($array_data);

        self::assertSame($expected_values['status_code'], $result->getStatusCode());
        self::assertSame($expected_values['body'], (string)$result->getBody());
    }

    #[Test]
    public function serializeAndDeserializeRoundTrip(): void
    {
        $original_response = new Response(
            404,
            ['Content-Type' => 'application/problem+json'],
            '{"type":"not-found","title":"Resource Not Found"}',
        );

        $serialized = $this->serializer->serialize($original_response);
        $deserialized = $this->serializer->deserialize($serialized);

        self::assertSame($original_response->getStatusCode(), $deserialized->getStatusCode());
        self::assertSame($original_response->getReasonPhrase(), $deserialized->getReasonPhrase());
        self::assertSame((string)$original_response->getBody(), (string)$deserialized->getBody());
        self::assertSame($original_response->getHeaders(), $deserialized->getHeaders());
    }

    #[Test]
    public function serializeHandlesEmptyBody(): void
    {
        $response = new Response(204);

        $result = $this->serializer->serialize($response);

        self::assertSame(204, $result['status_code']);
        self::assertSame('No Content', $result['reason_phrase']);
        self::assertSame('', $result['body']);
    }

    #[Test]
    public function serializeHandlesComplexHeaders(): void
    {
        $response = new Response(
            200,
            [
                'Content-Type' => 'application/hal+json; charset=utf-8',
                'Set-Cookie' => ['session=abc123; Path=/; Secure', 'csrf=xyz789; HttpOnly'],
                'Cache-Control' => 'public, max-age=3600',
                'ETag' => '"1234567890"',
            ],
        );

        $result = $this->serializer->serialize($response);

        self::assertArrayHasKey('headers', $result);
        self::assertIsArray($result['headers']);
    }

    #[Test]
    public function serializeHandlesCustomStatusCodes(): void
    {
        $response = new Response(422, [], '{"errors":["validation failed"]}');

        $result = $this->serializer->serialize($response);

        self::assertSame(422, $result['status_code']);
        self::assertSame('Unprocessable Entity', $result['reason_phrase']);
    }

    #[Test]
    public function serializeHandlesLargeResponseBody(): void
    {
        $large_body = \str_repeat('{"item":"data"}', 1000);
        $response = new Response(200, ['Content-Type' => 'application/json'], $large_body);

        $result = $this->serializer->serialize($response);

        self::assertSame($large_body, $result['body']);
    }

    #[Test]
    public function deserializeHandlesMinimalResponseData(): void
    {
        $array_data = [
            'status_code' => 200,
            'reason_phrase' => 'OK',
            'protocol_version' => '1.1',
            'headers' => [],
            'body' => '',
        ];

        $result = $this->serializer->deserialize($array_data);

        self::assertSame(200, $result->getStatusCode());
        self::assertSame('OK', $result->getReasonPhrase());
        self::assertSame('', (string)$result->getBody());
    }

    public static function provideValidResponses(): \Generator
    {
        yield 'successful response' => [
            new Response(200, [], '{"success":true}'),
            ['status_code' => 200, 'reason_phrase' => 'OK', 'body' => '{"success":true}'],
        ];

        yield 'created response' => [
            new Response(201, ['Location' => '/api/users/123'], '{"id":123}'),
            ['status_code' => 201, 'reason_phrase' => 'Created', 'body' => '{"id":123}'],
        ];

        yield 'not found response' => [
            new Response(404, [], '{"error":"Not Found"}'),
            ['status_code' => 404, 'reason_phrase' => 'Not Found', 'body' => '{"error":"Not Found"}'],
        ];

        yield 'server error response' => [
            new Response(500, [], '{"error":"Internal Server Error"}'),
            ['status_code' => 500, 'reason_phrase' => 'Internal Server Error', 'body' => '{"error":"Internal Server Error"}'],
        ];
    }

    public static function provideValidArrayData(): \Generator
    {
        yield 'minimal success' => [
            [
                'status_code' => 200,
                'reason_phrase' => 'OK',
                'protocol_version' => '1.1',
                'headers' => [],
                'body' => '',
            ],
            ['status_code' => 200, 'body' => ''],
        ];

        yield 'client error with body' => [
            [
                'status_code' => 400,
                'reason_phrase' => 'Bad Request',
                'protocol_version' => '1.1',
                'headers' => ['content-type' => ['application/problem+json']],
                'body' => '{"type":"validation-error"}',
            ],
            ['status_code' => 400, 'body' => '{"type":"validation-error"}'],
        ];

        yield 'redirect response' => [
            [
                'status_code' => 302,
                'reason_phrase' => 'Found',
                'protocol_version' => '1.1',
                'headers' => ['location' => ['/new-location']],
                'body' => '',
            ],
            ['status_code' => 302, 'body' => ''],
        ];
    }
}
