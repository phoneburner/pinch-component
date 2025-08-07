<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\ServerRequest;
use PhoneBurner\Pinch\Component\Http\Event\HandlingLogoutRequest;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HandlingLogoutRequestTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $request = new ServerRequest();
        $event = new HandlingLogoutRequest($request);

        self::assertSame($request, $event->request);
    }

    #[Test]
    public function getLogEntryReturnsLogEntryWithMessage(): void
    {
        $request = new ServerRequest();
        $event = new HandlingLogoutRequest($request);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame('Handling Logout Request', $log_entry->message);
        self::assertEmpty($log_entry->context);
    }
}
