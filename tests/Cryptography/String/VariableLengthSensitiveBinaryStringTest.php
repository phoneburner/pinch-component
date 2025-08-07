<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\String;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\String\VariableLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(VariableLengthSensitiveBinaryString::class)]
final class VariableLengthSensitiveBinaryStringTest extends TestCase
{
    private const string BYTES = "\xFF\xE0HelloWorld!";
    private const string HEX = 'ffe048656c6c6f576f726c6421';
    private const string BASE64 = '/+BIZWxsb1dvcmxkIQ==';
    private const string BASE64_NOPAD = '/+BIZWxsb1dvcmxkIQ';
    private const string BASE64_URLSAFE = '_-BIZWxsb1dvcmxkIQ==';
    private const string BASE64_URLSAFE_NOPAD = '_-BIZWxsb1dvcmxkIQ';

    #[Test]
    public function constructorWithRawStringStoresBytes(): void
    {
        $sensitive_string = new VariableLengthSensitiveBinaryString(self::BYTES);
        self::assertSame(self::BYTES, $sensitive_string->bytes());
        self::assertSame(\strlen(self::BYTES), $sensitive_string->length());
    }

    #[Test]
    public function destructorMethodWipesMemory(): void
    {
        $string = new VariableLengthSensitiveBinaryString(self::BYTES);
        $property = new \ReflectionClass($string)->getProperty('bytes');

        self::assertNotNull($property->getValue($string));
        $string->__destruct();
        self::assertNull($property->getValue($string));

        $this->expectException(CryptographicLogicException::class);
        $this->expectExceptionMessage('A code path was executed that would not normally be possible under normal operation.');
        $string->bytes();
    }

    #[Test]
    public function constructorWithBinaryStringStoresBytes(): void
    {
        $initial_string = Nonce::generate(384);
        $sensitive_string = new VariableLengthSensitiveBinaryString($initial_string);
        self::assertSame($initial_string->bytes(), $sensitive_string->bytes());
        self::assertSame($initial_string->length(), $sensitive_string->length());
    }

    #[Test]
    public function constructorWithEmptyString(): void
    {
        $sensitive_string = new VariableLengthSensitiveBinaryString('');
        self::assertSame('', $sensitive_string->bytes());
        self::assertSame(0, $sensitive_string->length());
    }

    #[Test]
    public function toStringReturnsDefaultEncoding(): void
    {
        $sensitive_string = new VariableLengthSensitiveBinaryString(self::BYTES);
        self::assertSame(self::BASE64_URLSAFE, (string)$sensitive_string);
        self::assertSame(self::BASE64_URLSAFE, $sensitive_string->export());
    }

    #[Test]
    #[DataProvider('provideEncodings')]
    public function exportReturnsCorrectValueForEncoding(Encoding $encoding, string $expected): void
    {
        $sensitive_string = new VariableLengthSensitiveBinaryString(self::BYTES);
        self::assertSame($expected, $sensitive_string->export($encoding));
    }

    #[Test]
    #[DataProvider('provideEncodings')]
    public function importReturnsCorrectValueForEncoding(Encoding $encoding, string $import): void
    {
        $sensitive_string = VariableLengthSensitiveBinaryString::import($import, $encoding);
        self::assertSame(self::BYTES, $sensitive_string->bytes());
    }

    #[Test]
    public function jsonSerializeReturnsDefaultEncoding(): void
    {
        $sensitive_string = new VariableLengthSensitiveBinaryString(self::BYTES);
        self::assertSame(self::BASE64_URLSAFE, $sensitive_string->jsonSerialize());
    }

    #[Test]
    public function unserializeRestoresOriginalBytes(): void
    {
        $original = new VariableLengthSensitiveBinaryString(self::BYTES);
        $serialized = \serialize($original);
        unset($original); // Attempt to trigger __destruct if possible, though effect isn't testable here

        $unserialized = \unserialize($serialized);
        self::assertInstanceOf(VariableLengthSensitiveBinaryString::class, $unserialized);
        self::assertSame(self::BYTES, $unserialized->bytes());
        self::assertSame(\strlen(self::BYTES), $unserialized->length());
    }

    public static function provideEncodings(): \Iterator
    {
        yield [Encoding::Hex, self::HEX];
        yield [Encoding::Base64, self::BASE64];
        yield [Encoding::Base64NoPadding, self::BASE64_NOPAD];
        yield [Encoding::Base64Url, self::BASE64_URLSAFE];
        yield [Encoding::Base64UrlNoPadding, self::BASE64_URLSAFE_NOPAD];
    }
}
