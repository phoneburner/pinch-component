<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Mailer;

use PhoneBurner\Pinch\Component\Mailer\MessageBody;
use PhoneBurner\Pinch\Component\Mailer\MessageBodyPart;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MessageBodyTest extends TestCase
{
    #[Test]
    public function constructorSetsProperties(): void
    {
        $html = new MessageBodyPart('<p>HTML content</p>', 'utf-8');
        $text = new MessageBodyPart('Plain text content', 'utf-8');

        $body = new MessageBody($html, $text);

        self::assertSame($html, $body->html);
        self::assertSame($text, $body->text);
    }

    #[Test]
    public function constructorAcceptsNullValues(): void
    {
        $html = new MessageBodyPart('<p>HTML content</p>', 'utf-8');
        $body1 = new MessageBody($html, null);
        self::assertSame($html, $body1->html);
        self::assertNull($body1->text);

        $text = new MessageBodyPart('Plain text content', 'utf-8');
        $body2 = new MessageBody(null, $text);
        self::assertNull($body2->html);
        self::assertSame($text, $body2->text);
    }

    #[Test]
    public function emptyConstructorCreatesNullBodyParts(): void
    {
        $body = new MessageBody();
        self::assertNull($body->html);
        self::assertNull($body->text);
    }
}
