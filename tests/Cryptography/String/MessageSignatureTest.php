<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\String;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidStringLength;
use PhoneBurner\Pinch\Component\Cryptography\String\MessageSignature;
use PhoneBurner\Pinch\Component\Cryptography\String\VariableLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageSignature::class)]
final class MessageSignatureTest extends TestCase
{
    private string $valid_bytes;

    protected function setUp(): void
    {
        $this->valid_bytes = \random_bytes(MessageSignature::LENGTH);
    }

    #[Test]
    public function happyPathTest(): void
    {
        $bytes = \random_bytes(MessageSignature::LENGTH);

        $signature = new MessageSignature($bytes);

        self::assertSame($bytes, $signature->bytes());
        self::assertSame(MessageSignature::LENGTH, $signature->length());

        $encoded = $signature->export();
        self::assertEquals($signature, MessageSignature::import($encoded));
        self::assertMatchesRegularExpression(Encoding::BASE64URL_REGEX, (string)$signature);
    }

    #[Test]
    public function invalidLengthTestShort(): void
    {
        $this->expectException(InvalidStringLength::class);
        new MessageSignature(\random_bytes(MessageSignature::LENGTH - 1));
    }

    #[Test]
    public function invalidLengthTestLong(): void
    {
        $this->expectException(InvalidStringLength::class);
        new MessageSignature(\random_bytes(MessageSignature::LENGTH + 1));
    }

    #[Test]
    public function invalidLengthTestEmpty(): void
    {
        $this->expectException(InvalidStringLength::class);
        new MessageSignature('');
    }

    #[Test]
    public function constructsWithValidBytes(): void
    {
        $signature = new MessageSignature($this->valid_bytes);
        self::assertInstanceOf(MessageSignature::class, $signature);
        self::assertSame($this->valid_bytes, $signature->bytes());
    }

    #[Test]
    public function throwsWhenBytesHaveInvalidLength(): void
    {
        $this->expectException(InvalidStringLength::class);
        $this->expectExceptionMessage('String Must Be Exactly 64 Bytes');

        new MessageSignature(\random_bytes(MessageSignature::LENGTH - 1));
    }

    #[Test]
    public function firstReturnsCorrectSubstring(): void
    {
        $signature = new MessageSignature($this->valid_bytes);
        $substring = $signature->first(10);

        self::assertInstanceOf(VariableLengthSensitiveBinaryString::class, $substring);
        self::assertSame(\substr($this->valid_bytes, 0, 10), $substring->bytes());
        self::assertSame(10, $substring->length());
    }

    #[Test]
    public function lengthConstantIsCorrect(): void
    {
        self::assertSame(64, MessageSignature::LENGTH);
        self::assertSame(\SODIUM_CRYPTO_GENERICHASH_BYTES_MAX, MessageSignature::LENGTH);
    }
}
