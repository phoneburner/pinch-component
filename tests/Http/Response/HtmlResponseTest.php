<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\HtmlResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HtmlResponseTest extends TestCase
{
    #[Test]
    public function createsHtmlResponseWithDefaults(): void
    {
        $html = '<html><body><h1>Test</h1></body></html>';
        $response = new HtmlResponse($html);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($html, (string)$response->getBody());
        self::assertSame(ContentType::HTML . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }

    #[Test]
    public function createsHtmlResponseWithCustomStatus(): void
    {
        $html = '<html><body><h1>Created</h1></body></html>';
        $response = new HtmlResponse($html, HttpStatus::CREATED);

        self::assertSame(HttpStatus::CREATED, $response->getStatusCode());
        self::assertSame($html, (string)$response->getBody());
        self::assertSame(ContentType::HTML . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }

    #[Test]
    public function createsHtmlResponseWithCustomHeaders(): void
    {
        $html = '<html><body><h1>Test</h1></body></html>';
        $headers = [
            'X-Custom-Header' => ['custom-value'],
            HttpHeader::CONTENT_LANGUAGE => ['en'],
        ];

        $response = new HtmlResponse($html, HttpStatus::OK, $headers);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($html, (string)$response->getBody());
        self::assertSame(ContentType::HTML . '; charset=utf-8', $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame(['custom-value'], $response->getHeader('X-Custom-Header'));
        self::assertSame(['en'], $response->getHeader(HttpHeader::CONTENT_LANGUAGE));
    }

    #[Test]
    public function contentTypeHeaderCanBeOverridden(): void
    {
        $html = '<html><body><h1>Test</h1></body></html>';
        $customContentType = 'text/xml';
        $headers = [
            HttpHeader::CONTENT_TYPE => $customContentType,
        ];

        $response = new HtmlResponse($html, HttpStatus::OK, $headers);

        self::assertSame(HttpStatus::OK, $response->getStatusCode());
        self::assertSame($html, (string)$response->getBody());
        self::assertSame($customContentType, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
    }
}
