<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Symmetric\Algorithm;

use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\Aes256Gcm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Filesystem\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class Aes256GcmTest extends TestCase
{
    public const string MESSAGE = 'The Quick Brown Fox Jumps Over The Lazy Dog';

    public const string KNOWN_KEY = 'pP8fF46Eb737WAN9ccW1iZJP3w/7GESMKgfWT38/aU0=';

    public const string ADDITIONAL_DATA = 'Some Random Metadata Not Sent in the Message';

    #[Test]
    public function encryptionHappyPath(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE);

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext);

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function symmetricEncryptionRegressionTest(): void
    {
        $shared_key = SharedKey::import(self::KNOWN_KEY);
        $ciphertext = CipherText::import(File::read(__DIR__ . '/../../Fixtures/lorem_aes256gcm.txt'));

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function aeadHappyPath(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE, self::ADDITIONAL_DATA);

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext, self::ADDITIONAL_DATA);

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function aeadMissingOnEncryption(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE);

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext, self::ADDITIONAL_DATA);

        self::assertNull($plaintext);
    }

    #[Test]
    public function aeadMissingOnDecryption(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE, self::ADDITIONAL_DATA);

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    public function aeadDoesNotMatch(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE, self::ADDITIONAL_DATA);

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext, 'Some Other Metadata');

        self::assertNull($plaintext);
    }

    #[Test]
    public function decryptReturnsNullWithWrongKey(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE);

        $wrong_key = SharedKey::generate();

        $plaintext = Aes256Gcm::decrypt($wrong_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    public function decryptReturnsNullWithWrongTag(): void
    {
        $shared_key = SharedKey::generate();

        $ciphertext = Aes256Gcm::encrypt($shared_key, self::MESSAGE);
        $ciphertext = new Ciphertext(\substr($ciphertext->bytes(), 0, -1));

        $plaintext = Aes256Gcm::decrypt($shared_key, $ciphertext);

        self::assertNull($plaintext);
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith(['short'])]
    public function decryptReturnsNullWhenMessageIsTooShort(string $ciphertext): void
    {
        // Pass a deliberately short message to trigger error condition.
        $plaintext = Aes256Gcm::decrypt(SharedKey::generate(), new Ciphertext($ciphertext));

        self::assertNull($plaintext);
    }
}
