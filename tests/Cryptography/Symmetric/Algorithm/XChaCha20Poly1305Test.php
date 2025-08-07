<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Symmetric\Algorithm;

use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\XChaCha20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Filesystem\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class XChaCha20Poly1305Test extends TestCase
{
    public const string MESSAGE = 'The Quick Brown Fox Jumps Over The Lazy Dog';

    public const string KNOWN_KEY = 'pP8fF46Eb737WAN9ccW1iZJP3w/7GESMKgfWT38/aU0=';

    public const string ADDITIONAL_DATA = 'Some Random Metadata Not Sent in the Message';

    #[Test]
    public function encryptionHappyPath(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE);

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function symmetricEncryptionRegressionTest(): void
    {
        $shared_key = SharedKey::import(self::KNOWN_KEY);
        $ciphertext = CipherText::import(File::read(__DIR__ . '/../../Fixtures/lorem_xchacha20poly1305.txt'));

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function aeadHappyPath(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE, self::ADDITIONAL_DATA);

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext, self::ADDITIONAL_DATA);

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function aeadMissingOnEncryption(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE);

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext, self::ADDITIONAL_DATA);

        self::assertNull($plaintext);
    }

    #[Test]
    public function aeadMissingOnDecryption(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE, self::ADDITIONAL_DATA);

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    public function aeadDoesNotMatch(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE, self::ADDITIONAL_DATA);

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext, 'Some Other Metadata');

        self::assertNull($plaintext);
    }

    #[Test]
    public function decryptReturnsNullWithWrongKey(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE);

        $wrong_key = SharedKey::generate();

        $plaintext = XChaCha20Poly1305::decrypt($wrong_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    public function decryptReturnsNullWithWrongTag(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = XChaCha20Poly1305::encrypt($shared_key, self::MESSAGE);
        $ciphertext = new Ciphertext(\substr($ciphertext->bytes(), 0, -1));

        $plaintext = XChaCha20Poly1305::decrypt($shared_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith(['short'])]
    public function decryptReturnsNullWhenMessageIsTooShort(string $ciphertext): void
    {
        // Pass a deliberately short message to trigger error condition.
        $plaintext = XChaCha20Poly1305::decrypt(SharedKey::generate(), new Ciphertext($ciphertext));

        self::assertNull($plaintext);
    }
}
