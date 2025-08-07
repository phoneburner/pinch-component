<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Mailer;

use PhoneBurner\Pinch\Component\EmailAddress\EmailAddress;
use PhoneBurner\Pinch\Component\Mailer\Attachment;
use PhoneBurner\Pinch\Component\Mailer\Email;
use PhoneBurner\Pinch\Component\Mailer\MessageBody;
use PhoneBurner\Pinch\Component\Mailer\MessageBodyPart;
use PhoneBurner\Pinch\Component\Mailer\Priority;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function constructorSetsSubjectAndDefaultPriority(): void
    {
        $email = new Email('Test Subject');

        self::assertSame('Test Subject', $email->getSubject());
        self::assertSame(Priority::Normal, $email->getPriority());
    }

    #[Test]
    public function constructorSetsCustomPriority(): void
    {
        $email = new Email('Test Subject', Priority::High);

        self::assertSame('Test Subject', $email->getSubject());
        self::assertSame(Priority::High, $email->getPriority());
    }

    #[Test]
    public function newEmailHasEmptyRecipients(): void
    {
        $email = new Email('Test Subject');

        self::assertEmpty($email->getTo());
        self::assertEmpty($email->getCc());
        self::assertEmpty($email->getBcc());
        self::assertEmpty($email->getFrom());
        self::assertEmpty($email->getReplyTo());
    }

    #[Test]
    public function addToAddsRecipients(): void
    {
        $email = new Email('Test Subject');
        $address1 = new EmailAddress('test1@example.com');
        $address2 = new EmailAddress('test2@example.com');

        $result = $email->addTo($address1, $address2);

        self::assertSame($email, $result);
        self::assertCount(2, $email->getTo());
        self::assertSame($address1, $email->getTo()['test1@example.com']);
        self::assertSame($address2, $email->getTo()['test2@example.com']);
    }

    #[Test]
    public function addCcAddsCcRecipients(): void
    {
        $email = new Email('Test Subject');
        $address1 = new EmailAddress('test1@example.com');
        $address2 = new EmailAddress('test2@example.com');

        $result = $email->addCc($address1, $address2);

        self::assertSame($email, $result);
        self::assertCount(2, $email->getCc());
        self::assertSame($address1, $email->getCc()['test1@example.com']);
        self::assertSame($address2, $email->getCc()['test2@example.com']);
    }

    #[Test]
    public function addBccAddsBccRecipients(): void
    {
        $email = new Email('Test Subject');
        $address1 = new EmailAddress('test1@example.com');
        $address2 = new EmailAddress('test2@example.com');

        $result = $email->addBcc($address1, $address2);

        self::assertSame($email, $result);
        self::assertCount(2, $email->getBcc());
        self::assertSame($address1, $email->getBcc()['test1@example.com']);
        self::assertSame($address2, $email->getBcc()['test2@example.com']);
    }

    #[Test]
    public function addFromAddsFromAddresses(): void
    {
        $email = new Email('Test Subject');
        $address1 = new EmailAddress('test1@example.com');
        $address2 = new EmailAddress('test2@example.com');

        $result = $email->addFrom($address1, $address2);

        self::assertSame($email, $result);
        self::assertCount(2, $email->getFrom());
        self::assertSame($address1, $email->getFrom()['test1@example.com']);
        self::assertSame($address2, $email->getFrom()['test2@example.com']);
    }

    #[Test]
    public function addReplyToAddsReplyToAddresses(): void
    {
        $email = new Email('Test Subject');
        $address1 = new EmailAddress('test1@example.com');
        $address2 = new EmailAddress('test2@example.com');

        $result = $email->addReplyTo($address1, $address2);

        self::assertSame($email, $result);
        self::assertCount(2, $email->getReplyTo());
        self::assertSame($address1, $email->getReplyTo()['test1@example.com']);
        self::assertSame($address2, $email->getReplyTo()['test2@example.com']);
    }

    #[Test]
    public function setTextBodySetsTextOnlyBody(): void
    {
        $email = new Email('Test Subject');

        $result = $email->setTextBody('Plain text content');

        self::assertSame($email, $result);
        self::assertNotNull($email->getBody());
        self::assertNull($email->getBody()->html);
        self::assertNotNull($email->getBody()->text);
        self::assertSame('Plain text content', $email->getBody()->text->contents);
        self::assertSame(MessageBodyPart::DEFAULT_CHARSET, $email->getBody()->text->charset);
    }

    #[Test]
    public function setHtmlBodySetsHtmlOnlyBody(): void
    {
        $email = new Email('Test Subject');

        $result = $email->setHtmlBody('<p>HTML content</p>');

        self::assertSame($email, $result);
        self::assertNotNull($email->getBody());
        self::assertNotNull($email->getBody()->html);
        self::assertNull($email->getBody()->text);
        self::assertSame('<p>HTML content</p>', $email->getBody()->html->contents);
        self::assertSame(MessageBodyPart::DEFAULT_CHARSET, $email->getBody()->html->charset);
    }

    #[Test]
    public function setTextBodyPreservesHtmlBody(): void
    {
        $email = new Email('Test Subject');
        $email->setHtmlBody('<p>HTML content</p>');

        $email->setTextBody('Plain text content');

        self::assertNotNull($email->getBody());
        self::assertNotNull($email->getBody()->html);
        self::assertNotNull($email->getBody()->text);
        self::assertSame('<p>HTML content</p>', $email->getBody()->html->contents);
        self::assertSame('Plain text content', $email->getBody()->text->contents);
    }

    #[Test]
    public function setHtmlBodyPreservesTextBody(): void
    {
        $email = new Email('Test Subject');
        $email->setTextBody('Plain text content');

        $email->setHtmlBody('<p>HTML content</p>');

        self::assertNotNull($email->getBody());
        self::assertNotNull($email->getBody()->html);
        self::assertNotNull($email->getBody()->text);
        self::assertSame('<p>HTML content</p>', $email->getBody()->html->contents);
        self::assertSame('Plain text content', $email->getBody()->text->contents);
    }

    #[Test]
    public function setBodySetsMessageBody(): void
    {
        $email = new Email('Test Subject');
        $body = new MessageBody(
            new MessageBodyPart('<p>HTML content</p>'),
            new MessageBodyPart('Plain text content'),
        );

        $result = $email->setBody($body);

        self::assertSame($email, $result);
        self::assertSame($body, $email->getBody());
    }

    #[Test]
    public function attachAddsAttachment(): void
    {
        $email = new Email('Test Subject');
        $attachment1 = Attachment::fromPath('test1.txt');
        $attachment2 = Attachment::fromContent('Test content', 'test2.txt');

        $result1 = $email->attach($attachment1);
        $result2 = $email->attach($attachment2);

        self::assertSame($email, $result1);
        self::assertSame($email, $result2);

        $attachments = $email->getAttachments();
        self::assertCount(2, $attachments);
        self::assertSame($attachment1, $attachments[0]);
        self::assertSame($attachment2, $attachments[1]);
    }
}
