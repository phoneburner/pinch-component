<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Logging;

use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel as Psr3LogLevel;

final class LogLevelTest extends TestCase
{
    #[Test]
    public function enumValuesMatchPsr3Values(): void
    {
        self::assertSame(Psr3LogLevel::EMERGENCY, LogLevel::Emergency->value);
        self::assertSame(Psr3LogLevel::ALERT, LogLevel::Alert->value);
        self::assertSame(Psr3LogLevel::CRITICAL, LogLevel::Critical->value);
        self::assertSame(Psr3LogLevel::ERROR, LogLevel::Error->value);
        self::assertSame(Psr3LogLevel::WARNING, LogLevel::Warning->value);
        self::assertSame(Psr3LogLevel::NOTICE, LogLevel::Notice->value);
        self::assertSame(Psr3LogLevel::INFO, LogLevel::Info->value);
        self::assertSame(Psr3LogLevel::DEBUG, LogLevel::Debug->value);
    }

    #[Test]
    #[DataProvider('instanceFromStringProvider')]
    public function instanceFromStringReturnsCorrectEnum(string $input, LogLevel $expected): void
    {
        $result = LogLevel::instance($input);
        self::assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('instanceFromIntProvider')]
    public function instanceFromIntReturnsCorrectEnum(int $input, LogLevel $expected): void
    {
        $result = LogLevel::instance($input);
        self::assertSame($expected, $result);
    }

    #[Test]
    public function instanceFromEnumReturnsSameInstance(): void
    {
        $level = LogLevel::Error;
        $result = LogLevel::instance($level);
        self::assertSame($level, $result);
    }

    #[Test]
    public function instanceFromObjectWithToPsrLogLevelMethod(): void
    {
        $object = new class {
            public function toPsrLogLevel(): string
            {
                return 'error';
            }
        };

        $result = LogLevel::instance($object);
        self::assertSame(LogLevel::Error, $result);
    }

    #[Test]
    public function instanceThrowsForInvalidInt(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LogLevel::instance(999);
    }

    #[Test]
    public function instanceThrowsForInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        LogLevel::instance([]);
    }

    #[Test]
    #[DataProvider('monologLevelProvider')]
    public function toMonlogLogLevelReturnsCorrectValue(LogLevel $level, int $expected): void
    {
        $result = $level->toMonlogLogLevel();
        self::assertSame($expected, $result);
    }

    /**
     * @return \Iterator<string, array{string, LogLevel}>
     */
    public static function instanceFromStringProvider(): \Iterator
    {
        yield 'emergency' => ['emergency', LogLevel::Emergency];
        yield 'alert' => ['alert', LogLevel::Alert];
        yield 'critical' => ['critical', LogLevel::Critical];
        yield 'error' => ['error', LogLevel::Error];
        yield 'warning' => ['warning', LogLevel::Warning];
        yield 'notice' => ['notice', LogLevel::Notice];
        yield 'info' => ['info', LogLevel::Info];
        yield 'debug' => ['debug', LogLevel::Debug];
        yield 'EMERGENCY uppercase' => ['EMERGENCY', LogLevel::Emergency];
        yield 'Error mixed case' => ['Error', LogLevel::Error];
        yield 'DEBUG uppercase' => ['DEBUG', LogLevel::Debug];
    }

    /**
     * @return \Iterator<string, array{int, LogLevel}>
     */
    public static function instanceFromIntProvider(): \Iterator
    {
        yield 'emergency (600)' => [600, LogLevel::Emergency];
        yield 'alert (550)' => [550, LogLevel::Alert];
        yield 'critical (500)' => [500, LogLevel::Critical];
        yield 'error (400)' => [400, LogLevel::Error];
        yield 'warning (300)' => [300, LogLevel::Warning];
        yield 'notice (250)' => [250, LogLevel::Notice];
        yield 'info (200)' => [200, LogLevel::Info];
        yield 'debug (100)' => [100, LogLevel::Debug];
    }

    /**
     * @return \Iterator<string, array{LogLevel, int}>
     */
    public static function monologLevelProvider(): \Iterator
    {
        yield 'emergency' => [LogLevel::Emergency, 600];
        yield 'alert' => [LogLevel::Alert, 550];
        yield 'critical' => [LogLevel::Critical, 500];
        yield 'error' => [LogLevel::Error, 400];
        yield 'warning' => [LogLevel::Warning, 300];
        yield 'notice' => [LogLevel::Notice, 250];
        yield 'info' => [LogLevel::Info, 200];
        yield 'debug' => [LogLevel::Debug, 100];
    }
}
