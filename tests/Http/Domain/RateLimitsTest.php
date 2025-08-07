<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Domain;

use PhoneBurner\Pinch\Component\Http\Domain\RateLimits;
use PhoneBurner\Pinch\Component\Http\Exception\InvalidRateLimits;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RateLimitsTest extends TestCase
{
    #[Test]
    public function constructorCreatesValidRateLimits(): void
    {
        $limits = new RateLimits(id: 'test', per_second: 5, per_minute: 100);

        self::assertSame('test', $limits->id);
        self::assertSame(5, $limits->per_second);
        self::assertSame(100, $limits->per_minute);
    }

    #[Test]
    public function constructorUsesDefaultValues(): void
    {
        $limits = new RateLimits(id: 'test');

        self::assertSame('test', $limits->id);
        self::assertSame(10, $limits->per_second);
        self::assertSame(60, $limits->per_minute);
    }

    #[Test]
    public function constructorThrowsExceptionForEmptyId(): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Rate limit ID cannot be empty');

        new RateLimits(id: '');
    }

    #[Test]
    #[DataProvider('invalidPerSecondProvider')]
    public function constructorThrowsExceptionForInvalidPerSecond(int $per_second): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Per-second limit must be positive');

        new RateLimits(id: 'test', per_second: $per_second);
    }

    #[Test]
    #[DataProvider('invalidPerMinuteProvider')]
    public function constructorThrowsExceptionForInvalidPerMinute(int $per_minute): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Per-minute limit must be positive');

        new RateLimits(id: 'test', per_minute: $per_minute);
    }

    #[Test]
    public function constructorThrowsExceptionWhenPerMinuteLessThanPerSecond(): void
    {
        $this->expectException(InvalidRateLimits::class);
        $this->expectExceptionMessage('Per-minute limit (5) cannot be less than per-second limit (10)');

        new RateLimits(id: 'test', per_second: 10, per_minute: 5);
    }

    public static function invalidPerSecondProvider(): \Iterator
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'very negative' => [-100];
    }

    public static function invalidPerMinuteProvider(): \Iterator
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
        yield 'very negative' => [-100];
    }
}
