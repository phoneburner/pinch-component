<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Symmetric;

use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\MessageSignature;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Symmetric;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PhoneBurner\Pinch\Filesystem\File;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SymmetricTest extends TestCase
{
    public const string KNOWN_KEY = 'pP8fF46Eb737WAN9ccW1iZJP3w/7GESMKgfWT38/aU0=';

    public const string KNOWN_MESSAGE_SIGNATURE = 'pNAWVyKWTX2WYheGkC9agrp0pqsh87bfdvWm3vI51CiljrO2liZE2nBPrhbWgaE84y-OzxkRcnCgYs9uvouwOg';

    private string $known_plaintext;

    protected function setUp(): void
    {
        $this->known_plaintext = File::read(__DIR__ . '/../Fixtures/known_plaintext.txt');
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function encryptAndDecryptWorkWithoutAdditionalData(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext);
        $decrypted = new Symmetric()->decrypt($key, $ciphertext);

        self::assertSame($plaintext, $decrypted);
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function encryptAndDecryptWorkWithAdditionalData(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext, 'additional data');
        $decrypted = new Symmetric()->decrypt($key, $ciphertext, 'additional data');

        self::assertSame($plaintext, $decrypted);
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function messageLengthIsChecked(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext);

        // cut off the last bytes, making it too short.
        $ciphertext = new Ciphertext(\substr($ciphertext->bytes(), 0, Symmetric::MIN_CIPHERTEXT_BYTES - 1));

        self::assertNull(new Symmetric()->decrypt($key, $ciphertext, '', SymmetricAlgorithm::XChaCha20Blake2b));
    }

    #[Test]
    #[DataProvider('providesPlaintextTestCases')]
    public function messageAuthenticationWorks(string $plaintext): void
    {
        $key = SharedKey::generate();
        $ciphertext = new Symmetric()->encrypt($key, $plaintext);

        // change one byte in the authentication tag
        $bytes = $ciphertext->bytes();
        $length = \strlen($bytes);
        $bytes[$length - 4] = $bytes[$length - 4] === 'a' ? 'b' : 'a';

        $plaintext = new Symmetric()->decrypt($key, new Ciphertext($bytes));

        self::assertNull($plaintext);
    }

    public static function providesPlaintextTestCases(): iterable
    {
        yield 'HelloWorld' => ['Hello World'];
        yield 'EmptyString' => [''];
        yield 'LoremIpsum' => [File::read(__DIR__ . '/../Fixtures/known_plaintext.txt')];
    }

    #[Test]
    public function signAndVerifyReturnTrueWithSameKeys(): void
    {
        $key = SharedKey::generate();

        $message_signature = new Symmetric()->sign($key, $this->known_plaintext);

        self::assertSame(MessageSignature::LENGTH, \strlen($message_signature->bytes()));
        self::assertTrue(new Symmetric()->verify($key, $message_signature, $this->known_plaintext));
    }

    #[Test]
    public function signAndVerifyReturnFalseWithDifferentKeys(): void
    {
        $key = SharedKey::generate();

        $message_signature = new Symmetric()->sign($key, $this->known_plaintext);

        self::assertSame(MessageSignature::LENGTH, \strlen($message_signature->bytes()));
        self::assertFalse(new Symmetric()->verify(SharedKey::generate(), $message_signature, $this->known_plaintext));
    }

    #[Test]
    public function verifyRegressionTest(): void
    {
        $key = SharedKey::import(self::KNOWN_KEY);
        $message_signature = MessageSignature::import(self::KNOWN_MESSAGE_SIGNATURE);

        self::assertTrue(new Symmetric()->verify($key, $message_signature, $this->known_plaintext));
    }
}
