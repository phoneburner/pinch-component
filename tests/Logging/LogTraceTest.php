<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Logging;

use PhoneBurner\Pinch\Component\Logging\LogTrace;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class LogTraceTest extends TestCase
{
    #[Test]
    public function itIsAUUID(): void
    {
        $log_trace = LogTrace::make();

        $uuid = $log_trace->uuid();

        self::assertTrue($log_trace->equals($uuid));
        self::assertSame(0, $log_trace->compareTo($uuid));
        self::assertSame(0, $uuid->compareTo($log_trace));
        self::assertTrue($log_trace->equals($uuid));
        self::assertTrue($log_trace->equals($log_trace));
        self::assertTrue($uuid->equals($log_trace));
        self::assertSame($uuid->getBytes(), $log_trace->getBytes());
        self::assertEquals($uuid->getFields(), $log_trace->getFields());
        self::assertEquals($uuid->getHex(), $log_trace->getHex());
        self::assertEquals($uuid->getInteger(), $log_trace->getInteger());
        self::assertSame($uuid->toString(), $log_trace->toString());
        self::assertSame((string)$uuid, (string)$log_trace);

        $deserialized = \unserialize(\serialize($log_trace));
        self::assertInstanceOf(LogTrace::class, $deserialized);
        self::assertSame($log_trace->toString(), $deserialized->toString());
        self::assertTrue($deserialized->equals($uuid));
        self::assertSame(0, $deserialized->compareTo($uuid));
        self::assertSame(0, $uuid->compareTo($deserialized));
        self::assertTrue($deserialized->equals($uuid));
        self::assertTrue($deserialized->equals($log_trace));
        self::assertTrue($uuid->equals($deserialized));
        self::assertSame($uuid->getBytes(), $deserialized->getBytes());
        self::assertEquals($uuid->getFields(), $deserialized->getFields());
        self::assertEquals($uuid->getHex(), $deserialized->getHex());
        self::assertEquals($uuid->getInteger(), $deserialized->getInteger());
        self::assertSame($uuid->toString(), $deserialized->toString());
        self::assertSame((string)$uuid, (string)$deserialized);
    }
}
