<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\RedirectResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RedirectResponseTest extends TestCase
{
    #[Test]
    public function createsRedirectWithDefaultStatus(): void
    {
        $uri = 'https://example.com/destination';
        $response = new RedirectResponse($uri);

        self::assertSame(HttpStatus::FOUND, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame($uri, $response->getHeaderLine(HttpHeader::LOCATION));
    }

    #[Test]
    #[DataProvider('redirectStatusCodes')]
    public function createsRedirectWithCustomStatus(int $status): void
    {
        $uri = 'https://example.com/destination';
        $response = new RedirectResponse($uri, $status);

        self::assertSame($status, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame($uri, $response->getHeaderLine(HttpHeader::LOCATION));
    }

    #[Test]
    public function createsRedirectWithCustomHeaders(): void
    {
        $uri = 'https://example.com/destination';
        $headers = [
            'X-Custom-Header' => ['custom-value'],
            HttpHeader::CACHE_CONTROL => ['no-cache'],
        ];

        $response = new RedirectResponse($uri, HttpStatus::FOUND, $headers);

        self::assertSame(HttpStatus::FOUND, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame($uri, $response->getHeaderLine(HttpHeader::LOCATION));
        self::assertSame(['custom-value'], $response->getHeader('X-Custom-Header'));
        self::assertSame(['no-cache'], $response->getHeader(HttpHeader::CACHE_CONTROL));
    }

    /**
     * @return \Iterator<(int | string), array<int>>
     */
    public static function redirectStatusCodes(): \Iterator
    {
        yield [HttpStatus::MOVED_PERMANENTLY];
        yield [HttpStatus::FOUND];
        yield [HttpStatus::SEE_OTHER];
        yield [HttpStatus::TEMPORARY_REDIRECT];
        yield [HttpStatus::PERMANENT_REDIRECT];
    }
}
