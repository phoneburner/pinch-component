<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Message;

use GuzzleHttp\Psr7\Request;
use PhoneBurner\Pinch\Component\Http\Message\RequestArraySerializer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(RequestArraySerializer::class)]
final class RequestArraySerializerTest extends TestCase
{
    private RequestArraySerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new RequestArraySerializer();
    }

    #[Test]
    public function serializeConvertsRequestToArray(): void
    {
        $request = new Request(
            'POST',
            '/api/users',
            ['Content-Type' => 'application/json', 'Accept' => 'application/hal+json'],
            '{"name":"John Doe"}',
        );

        $result = $this->serializer->serialize($request);

        self::assertIsArray($result);
        self::assertSame('POST', $result['method']);
        self::assertSame('/api/users', $result['request_target']);
        self::assertSame('/api/users', $result['uri']);
        self::assertSame('1.1', $result['protocol_version']);
        self::assertArrayHasKey('headers', $result);
        self::assertSame('{"name":"John Doe"}', $result['body']);
    }

    #[Test]
    #[DataProvider('provideValidRequests')]
    public function serializeHandlesVariousRequestTypes(RequestInterface $request, array $expected_values): void
    {
        $result = $this->serializer->serialize($request);

        self::assertSame($expected_values['method'], $result['method']);
        self::assertSame($expected_values['uri'], $result['uri']);
        self::assertSame($expected_values['body'], $result['body']);
    }

    #[Test]
    public function serializeThrowsExceptionForNonRequestMessage(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Message must be an instance of ResponseInterface');

        /** @phpstan-ignore argument.type */
        $this->serializer->serialize($response);
    }

    #[Test]
    public function deserializeConvertsArrayToRequest(): void
    {
        $array_data = [
            'method' => 'GET',
            'request_target' => '/api/users/123',
            'uri' => '/api/users/123',
            'protocol_version' => '1.1',
            'headers' => [
                'host' => ['example.com'],
                'accept' => ['application/json'],
            ],
            'body' => '',
        ];

        $result = $this->serializer->deserialize($array_data);

        self::assertInstanceOf(RequestInterface::class, $result);
        self::assertSame('GET', $result->getMethod());
        self::assertSame('/api/users/123', (string)$result->getUri());
        self::assertSame('', (string)$result->getBody());
        self::assertSame(['application/json'], $result->getHeader('accept'));
    }

    #[Test]
    #[DataProvider('provideValidArrayData')]
    public function deserializeHandlesVariousArrayStructures(array $array_data, array $expected_values): void
    {
        $result = $this->serializer->deserialize($array_data);

        self::assertSame($expected_values['method'], $result->getMethod());
        self::assertSame($expected_values['body'], (string)$result->getBody());
    }

    #[Test]
    public function serializeAndDeserializeRoundTrip(): void
    {
        $original_request = new Request(
            'PUT',
            '/api/users/456',
            ['Content-Type' => 'application/json'],
            '{"email":"updated@example.com"}',
        );

        $serialized = $this->serializer->serialize($original_request);
        $deserialized = $this->serializer->deserialize($serialized);

        self::assertSame($original_request->getMethod(), $deserialized->getMethod());
        self::assertSame((string)$original_request->getUri(), (string)$deserialized->getUri());
        self::assertSame((string)$original_request->getBody(), (string)$deserialized->getBody());
        self::assertSame($original_request->getHeaders(), $deserialized->getHeaders());
    }

    #[Test]
    public function serializeHandlesEmptyBody(): void
    {
        $request = new Request('DELETE', '/api/users/789');

        $result = $this->serializer->serialize($request);

        self::assertSame('DELETE', $result['method']);
        self::assertSame('', $result['body']);
    }

    #[Test]
    public function serializeHandlesComplexHeaders(): void
    {
        $request = new Request(
            'POST',
            '/api/upload',
            [
                'Content-Type' => 'multipart/form-data; boundary=something',
                'Accept' => ['application/json', 'application/xml'],
                'Authorization' => 'Bearer token123',
            ],
        );

        $result = $this->serializer->serialize($request);

        self::assertArrayHasKey('headers', $result);
        self::assertIsArray($result['headers']);
    }

    public static function provideValidRequests(): \Generator
    {
        yield 'GET request' => [
            new Request('GET', '/api/status'),
            ['method' => 'GET', 'uri' => '/api/status', 'body' => ''],
        ];

        yield 'POST with JSON body' => [
            new Request('POST', '/api/create', [], '{"data":"test"}'),
            ['method' => 'POST', 'uri' => '/api/create', 'body' => '{"data":"test"}'],
        ];

        yield 'PUT with form data' => [
            new Request('PUT', '/api/update', [], 'name=value&type=form'),
            ['method' => 'PUT', 'uri' => '/api/update', 'body' => 'name=value&type=form'],
        ];
    }

    public static function provideValidArrayData(): \Generator
    {
        yield 'minimal GET' => [
            [
                'method' => 'GET',
                'request_target' => '/',
                'uri' => '/',
                'protocol_version' => '1.1',
                'headers' => [],
                'body' => '',
            ],
            ['method' => 'GET', 'body' => ''],
        ];

        yield 'POST with body' => [
            [
                'method' => 'POST',
                'request_target' => '/submit',
                'uri' => '/submit',
                'protocol_version' => '1.1',
                'headers' => ['content-type' => ['application/json']],
                'body' => '{"submitted":true}',
            ],
            ['method' => 'POST', 'body' => '{"submitted":true}'],
        ];
    }
}
