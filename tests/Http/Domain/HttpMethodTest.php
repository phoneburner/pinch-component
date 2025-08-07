<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Domain;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
}
