<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\TextResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextResponseTest extends TestCase
{
    #[Test]
    public function createsTextResponseWithDefaults(): void
    {
        $text = 'Plain text message';
        $response = new TextResponse($text);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($text, (string)$response->getBody());
        self::assertSame(ContentType::TEXT . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }

    #[Test]
    public function createsTextResponseWithCustomStatus(): void
    {
        $text = 'Resource created successfully';
        $response = new TextResponse($text, HttpStatus::CREATED);

        self::assertSame(HttpStatus::CREATED, $response->getStatusCode());
        self::assertSame($text, (string)$response->getBody());
        self::assertSame(ContentType::TEXT . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }

    #[Test]
    public function createsTextResponseWithCustomHeaders(): void
    {
        $text = 'Plain text with custom headers';
        $headers = [
            'X-Custom-Header' => ['custom-value'],
            HttpHeader::CACHE_CONTROL => ['no-cache'],
        ];

        $response = new TextResponse($text, HttpStatus::OK, $headers);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($text, (string)$response->getBody());
        self::assertSame(ContentType::TEXT . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame(['custom-value'], $response->getHeader('X-Custom-Header'));
        self::assertSame(['no-cache'], $response->getHeader(HttpHeader::CACHE_CONTROL));
    }

    #[Test]
    public function contentTypeHeaderCanBeOverridden(): void
    {
        $text = 'Plain text with custom content type';
        $customContentType = 'text/csv';
        $headers = [
            HttpHeader::CONTENT_TYPE => $customContentType,
        ];

        $response = new TextResponse($text, HttpStatus::OK, $headers);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($text, (string)$response->getBody());
        self::assertSame($customContentType, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }
}
