<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response\Exceptional\TransformerStrategies;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\NotFoundResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\TransformerStrategies\TextResponseTransformerStrategy;
use PhoneBurner\Pinch\Component\Http\Response\TextResponse;
use PhoneBurner\Pinch\Component\Logging\LogTrace;
use PhoneBurner\Pinch\Uuid\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class TextResponseTransformerStrategyTest extends TestCase
{
    private TextResponseTransformerStrategy $strategy;

    private LogTrace $log_trace;

    protected function setUp(): void
    {
        $this->strategy = new TextResponseTransformerStrategy();
        $this->log_trace = new LogTrace(Uuid::instance('d1dd4364-d933-4cb3-b158-6340ccd35d47'));
    }

    #[Test]
    public function transformCreatesApiProblemResponse(): void
    {
        $exception = new NotFoundResponse();
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->strategy->transform($exception, $request, $this->log_trace);

        self::assertInstanceOf(TextResponse::class, $response);
        self::assertSame(HttpStatus::NOT_FOUND, $response->getStatusCode());
        self::assertSame(ContentType::TEXT . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame('HTTP 404: Not Found', (string)$response->getBody());
    }
}
