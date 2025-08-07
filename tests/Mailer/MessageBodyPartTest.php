<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Mailer;

use PhoneBurner\Pinch\Component\Mailer\MessageBodyPart;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessageBodyPartTest extends TestCase
{
    #[Test]
    public function constructorSetsProperties(): void
    {
        $part = new MessageBodyPart('Test content', 'iso-8859-1');

        self::assertSame('Test content', $part->contents);
        self::assertSame('iso-8859-1', $part->charset);
    }

    #[Test]
    public function constructorUsesDefaultCharsetWhenNotSpecified(): void
    {
        $part = new MessageBodyPart('Test content');

        self::assertSame('Test content', $part->contents);
        self::assertSame(MessageBodyPart::DEFAULT_CHARSET, $part->charset);
        self::assertSame('utf-8', $part->charset);
    }

    #[Test]
    public function emptyConstructorCreatesEmptyContent(): void
    {
        $part = new MessageBodyPart();

        self::assertSame('', $part->contents);
        self::assertSame(MessageBodyPart::DEFAULT_CHARSET, $part->charset);
    }
}
