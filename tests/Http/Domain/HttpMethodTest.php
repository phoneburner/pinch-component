<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Domain;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethodMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(HttpMethod::class)]
#[CoversClass(HttpMethodMetadata::class)]
final class HttpMethodTest extends TestCase
{
    #[Test]
    public function valuesReturnsExpectedValues(): void
    {
        self::assertSame([
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'DELETE',
            'CONNECT',
            'OPTIONS',
            'TRACE',
            'PATCH',
        ], HttpMethod::values());
    }

    #[Test]
    public function instanceReturnsExpectedInstance(): void
    {
        foreach (HttpMethod::cases() as $case) {
            self::assertSame($case, HttpMethod::instance($case));
            self::assertSame($case, HttpMethod::instance(\strtoupper($case->value)));
            self::assertSame($case, HttpMethod::instance(\strtolower($case->value)));
        }
    }

    #[Test]
    public function metadataReturnsExpectedMetadata(): void
    {
        // GET: pure, idempotent, cacheable
        $metadata = HttpMethod::Get->metadata();
        self::assertTrue($metadata->pure);
        self::assertTrue($metadata->cacheable);
        self::assertTrue($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Get->metadata());

        // HEAD: pure, idempotent, cacheable
        $metadata = HttpMethod::Head->metadata();
        self::assertTrue($metadata->pure);
        self::assertTrue($metadata->cacheable);
        self::assertTrue($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Head->metadata());

        // POST: not pure, not idempotent, cacheable
        $metadata = HttpMethod::Post->metadata();
        self::assertFalse($metadata->pure);
        self::assertTrue($metadata->cacheable);
        self::assertFalse($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Post->metadata());

        // PUT: not pure, idempotent, not cacheable
        $metadata = HttpMethod::Put->metadata();
        self::assertFalse($metadata->pure);
        self::assertFalse($metadata->cacheable);
        self::assertTrue($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Put->metadata());

        // DELETE: not pure, idempotent, not cacheable
        $metadata = HttpMethod::Delete->metadata();
        self::assertFalse($metadata->pure);
        self::assertFalse($metadata->cacheable);
        self::assertTrue($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Delete->metadata());

        // CONNECT: not pure, not idempotent, not cacheable
        $metadata = HttpMethod::Connect->metadata();
        self::assertFalse($metadata->pure);
        self::assertFalse($metadata->cacheable);
        self::assertFalse($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Connect->metadata());

        // OPTIONS: pure, idempotent, not cacheable
        $metadata = HttpMethod::Options->metadata();
        self::assertTrue($metadata->pure);
        self::assertFalse($metadata->cacheable);
        self::assertTrue($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Options->metadata());

        // TRACE: pure, idempotent, not cacheable
        $metadata = HttpMethod::Trace->metadata();
        self::assertTrue($metadata->pure);
        self::assertFalse($metadata->cacheable);
        self::assertTrue($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Trace->metadata());

        // PATCH: not pure, not idempotent, not cacheable
        $metadata = HttpMethod::Patch->metadata();
        self::assertFalse($metadata->pure);
        self::assertFalse($metadata->cacheable);
        self::assertFalse($metadata->idempotent);
        self::assertSame($metadata, HttpMethod::Patch->metadata());
    }
}
