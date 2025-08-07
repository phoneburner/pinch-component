<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestComplete;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestFailed;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestStart;
use PhoneBurner\Pinch\Component\HttpClient\Exception\HttpClientException;
use PhoneBurner\Pinch\Component\HttpClient\HttpClientWrapper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;

final class HttpClientWrapperTest extends TestCase
{
    private MockObject&ClientInterface $mock_client;
    private MockObject&EventDispatcherInterface $mock_event_dispatcher;
    private HttpClientWrapper $http_client;

    protected function setUp(): void
    {
        $this->mock_client = $this->createMock(ClientInterface::class);
        $this->mock_event_dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->http_client = new HttpClientWrapper($this->mock_client, $this->mock_event_dispatcher);
    }

    #[Test]
    public function constructorSetsProperties(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $event_dispatcher = $this->createMock(EventDispatcherInterface::class);
        $http_client = new HttpClientWrapper($client, $event_dispatcher);

        self::assertInstanceOf(HttpClientWrapper::class, $http_client);
    }

    #[Test]
    public function sendRequestEmitsStartEventBeforeRequest(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $response = new Response();

        $this->mock_event_dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(self::callback(static function ($event) use ($request): bool {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return $event instanceof HttpClientRequestStart && $event->request === $request;
                }

                return $event instanceof HttpClientRequestComplete;
            }));

        $this->mock_client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $result = $this->http_client->sendRequest($request);

        self::assertSame($response, $result);
    }

    #[Test]
    public function sendRequestEmitsCompleteEventAfterSuccessfulRequest(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $response = new Response();

        $this->mock_event_dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(self::callback(static function ($event) use ($request, $response): bool {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return $event instanceof HttpClientRequestStart;
                }

                return $event instanceof HttpClientRequestComplete
                    && $event->request === $request
                    && $event->response === $response;
            }));

        $this->mock_client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $result = $this->http_client->sendRequest($request);

        self::assertSame($response, $result);
    }

    #[Test]
    public function sendRequestEmitsFailedEventWhenExceptionThrown(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $exception = new HttpClientException('Request failed');

        $this->mock_event_dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(self::callback(static function ($event) use ($request, $exception): bool {
                static $callCount = 0;
                ++$callCount;

                if ($callCount === 1) {
                    return $event instanceof HttpClientRequestStart;
                }

                return $event instanceof HttpClientRequestFailed
                    && $event->request === $request
                    && $event->exception === $exception;
            }));

        $this->mock_client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willThrowException($exception);

        $this->expectExceptionObject($exception);

        $this->http_client->sendRequest($request);
    }

    #[Test]
    #[DataProvider('requestMethodProvider')]
    public function sendRequestHandlesDifferentHttpMethods(HttpMethod $method): void
    {
        $request = new Request('https://example.com', $method->value);
        $response = new Response();

        $this->mock_client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->mock_event_dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch');

        $result = $this->http_client->sendRequest($request);

        self::assertSame($response, $result);
    }

    /**
     * @return \Iterator<string, array{HttpMethod}>
     */
    public static function requestMethodProvider(): \Iterator
    {
        yield 'GET' => [HttpMethod::Get];
        yield 'POST' => [HttpMethod::Post];
        yield 'PUT' => [HttpMethod::Put];
        yield 'DELETE' => [HttpMethod::Delete];
        yield 'PATCH' => [HttpMethod::Patch];
        yield 'HEAD' => [HttpMethod::Head];
        yield 'OPTIONS' => [HttpMethod::Options];
    }

    #[Test]
    public function sendRequestPreservesRequestAndResponseObjects(): void
    {
        $request = new Request('https://example.com/api/test', HttpMethod::Post->value);
        $response = new Response(status: 201);

        $this->mock_client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $this->mock_event_dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch');

        $result = $this->http_client->sendRequest($request);

        self::assertSame($response, $result);
        self::assertSame('https://example.com/api/test', (string)$request->getUri());
        self::assertSame(201, $response->getStatusCode());
    }
}
