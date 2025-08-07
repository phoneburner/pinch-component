<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Mailer;

use PhoneBurner\Pinch\Component\Mailer\Attachment;
use PhoneBurner\Pinch\Component\Mailer\AttachmentType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AttachmentTest extends TestCase
{
    #[Test]
    public function constructorThrowsExceptionWhenNoPathOrContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Attachment must have a file path or content');
        new Attachment();
    }

    #[Test]
    public function constructorThrowsExceptionWhenBothPathAndContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Attachment cannot have both a file path and content');
        new Attachment('path', 'content');
    }

    #[Test]
    public function attachmentFromPathSetsCorrectType(): void
    {
        $attachment = new Attachment('test.txt');
        self::assertSame('test.txt', $attachment->path);
        self::assertSame('', $attachment->content);
        self::assertNull($attachment->name);
        self::assertNull($attachment->content_type);
        self::assertFalse($attachment->inline);
        self::assertSame(AttachmentType::AttachFromPath, $attachment->type);
    }

    #[Test]
    public function attachmentFromContentSetsCorrectType(): void
    {
        $attachment = new Attachment('', 'test content');
        self::assertSame('', $attachment->path);
        self::assertSame('test content', $attachment->content);
        self::assertNull($attachment->name);
        self::assertNull($attachment->content_type);
        self::assertFalse($attachment->inline);
        self::assertSame(AttachmentType::AttachFromContent, $attachment->type);
    }

    #[Test]
    public function inlineAttachmentFromPathSetsCorrectType(): void
    {
        $attachment = new Attachment('test.txt', '', null, null, true);
        self::assertSame('test.txt', $attachment->path);
        self::assertSame('', $attachment->content);
        self::assertNull($attachment->name);
        self::assertNull($attachment->content_type);
        self::assertTrue($attachment->inline);
        self::assertSame(AttachmentType::EmbedFromPath, $attachment->type);
    }

    #[Test]
    public function inlineAttachmentFromContentSetsCorrectType(): void
    {
        $attachment = new Attachment('', 'test content', null, null, true);
        self::assertSame('', $attachment->path);
        self::assertSame('test content', $attachment->content);
        self::assertNull($attachment->name);
        self::assertNull($attachment->content_type);
        self::assertTrue($attachment->inline);
        self::assertSame(AttachmentType::EmbedFromContent, $attachment->type);
    }

    #[Test]
    public function fromPathFactoryMethodCreatesPathAttachment(): void
    {
        $attachment = Attachment::fromPath('test.txt', 'custom.txt', 'text/plain', false);
        self::assertSame('test.txt', $attachment->path);
        self::assertSame('', $attachment->content);
        self::assertSame('custom.txt', $attachment->name);
        self::assertSame('text/plain', $attachment->content_type);
        self::assertFalse($attachment->inline);
        self::assertSame(AttachmentType::AttachFromPath, $attachment->type);
    }

    #[Test]
    public function fromContentFactoryMethodCreatesContentAttachment(): void
    {
        $attachment = Attachment::fromContent('test content', 'custom.txt', 'text/plain', false);
        self::assertSame('', $attachment->path);
        self::assertSame('test content', $attachment->content);
        self::assertSame('custom.txt', $attachment->name);
        self::assertSame('text/plain', $attachment->content_type);
        self::assertFalse($attachment->inline);
        self::assertSame(AttachmentType::AttachFromContent, $attachment->type);
    }

    #[Test]
    public function fromPathFactoryMethodCreatesInlineAttachment(): void
    {
        $attachment = Attachment::fromPath('test.txt', 'custom.txt', 'text/plain', true);
        self::assertSame('test.txt', $attachment->path);
        self::assertSame('', $attachment->content);
        self::assertSame('custom.txt', $attachment->name);
        self::assertSame('text/plain', $attachment->content_type);
        self::assertTrue($attachment->inline);
        self::assertSame(AttachmentType::EmbedFromPath, $attachment->type);
    }

    #[Test]
    public function fromContentFactoryMethodCreatesInlineAttachment(): void
    {
        $attachment = Attachment::fromContent('test content', 'custom.txt', 'text/plain', true);
        self::assertSame('', $attachment->path);
        self::assertSame('test content', $attachment->content);
        self::assertSame('custom.txt', $attachment->name);
        self::assertSame('text/plain', $attachment->content_type);
        self::assertTrue($attachment->inline);
        self::assertSame(AttachmentType::EmbedFromContent, $attachment->type);
    }
}
