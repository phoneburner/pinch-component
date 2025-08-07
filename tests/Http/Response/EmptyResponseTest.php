<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Response;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Response\EmptyResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmptyResponseTest extends TestCase
{
    #[Test]
    public function defaultConstructorCreates204Response(): void
    {
        $response = new EmptyResponse();

        self::assertSame(HttpStatus::NO_CONTENT, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame('', $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
    }

    #[Test]
    #[DataProvider('validStatusCodes')]
    public function canCreateWithCustomStatusCode(int $status): void
    {
        $response = new EmptyResponse($status);

        self::assertSame($status, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame('', $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
    }

    #[Test]
    public function canCreateWithCustomHeaders(): void
    {
        $headers = [
            'X-Custom-Header' => ['custom-value'],
            'X-Another-Header' => ['another-value'],
        ];

        $response = new EmptyResponse(HttpStatus::NO_CONTENT, $headers);

        self::assertSame(HttpStatus::NO_CONTENT, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame('', $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
        self::assertSame(['custom-value'], $response->getHeader('X-Custom-Header'));
        self::assertSame(['another-value'], $response->getHeader('X-Another-Header'));
    }

    /**
     * @return \Iterator<(int | string), array<int>>
     */
    public static function validStatusCodes(): \Iterator
    {
        yield [HttpStatus::OK];
        yield [HttpStatus::CREATED];
        yield [HttpStatus::ACCEPTED];
        yield [HttpStatus::NO_CONTENT];
        yield [HttpStatus::RESET_CONTENT];
    }
}
