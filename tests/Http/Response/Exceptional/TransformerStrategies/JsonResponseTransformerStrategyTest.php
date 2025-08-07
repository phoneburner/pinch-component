<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional\TransformerStrategies;

use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\ApiProblemResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\HttpExceptionResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use PhoneBurner\Pinch\Component\Logging\LogTrace;
use PhoneBurner\Pinch\String\Encoding\Json;
use PhoneBurner\Pinch\Uuid\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class JsonResponseTransformerStrategyTest extends TestCase
{
    private JsonResponseTransformerStrategy $strategy;

    private LogTrace $log_trace;

    protected function setUp(): void
    {
        $this->strategy = new JsonResponseTransformerStrategy();
        $this->log_trace = new LogTrace(Uuid::instance('d1dd4364-d933-4cb3-b158-6340ccd35d47'));
    }

    #[Test]
    public function transformCreatesApiProblemResponse(): void
    {
        $exception = $this->createMock(HttpExceptionResponse::class);
        $exception->method('getStatusCode')->willReturn(HttpStatus::NOT_FOUND);
        $exception->method('getStatusTitle')->willReturn('Not Found');
        $exception->method('getStatusDetail')->willReturn('The requested resource was not found');
        $exception->method('getHeaders')->willReturn(['X-Test' => ['test-value']]);
        $exception->method('getAdditional')->willReturn(['path' => '/users/123']);

        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->strategy->transform($exception, $request, $this->log_trace);

        self::assertInstanceOf(ApiProblemResponse::class, $response);
        self::assertSame(HttpStatus::NOT_FOUND, $response->getStatusCode());
        self::assertSame(ContentType::PROBLEM_DETAILS_JSON, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame(['test-value'], $response->getHeader('X-Test'));

        $body = Json::decode((string)$response->getBody());
        self::assertSame(HttpStatus::NOT_FOUND, $body['status']);
        self::assertSame('Not Found', $body['title']);
        self::assertSame('The requested resource was not found', $body['detail']);
        self::assertSame('/users/123', $body['path']);
        self::assertSame('d1dd4364-d933-4cb3-b158-6340ccd35d47', $body['log_trace']);
        self::assertSame('https://httpstatuses.io/404', $body['type']);
    }

    #[Test]
    public function transformHandlesNullDetail(): void
    {
        $exception = $this->createMock(HttpExceptionResponse::class);
        $exception->method('getStatusCode')->willReturn(HttpStatus::NOT_FOUND);
        $exception->method('getStatusTitle')->willReturn('Not Found');
        $exception->method('getStatusDetail')->willReturn('');
        $exception->method('getHeaders')->willReturn([]);
        $exception->method('getAdditional')->willReturn([]);

        $request = new ServerRequest();

        $response = $this->strategy->transform($exception, $request, $this->log_trace);

        $body = Json::decode((string)$response->getBody());
        self::assertSame(HttpStatus::NOT_FOUND, $body['status']);
        self::assertSame('Not Found', $body['title']);
        self::assertNull($body['detail']);
        self::assertSame('d1dd4364-d933-4cb3-b158-6340ccd35d47', $body['log_trace']);
    }
}
