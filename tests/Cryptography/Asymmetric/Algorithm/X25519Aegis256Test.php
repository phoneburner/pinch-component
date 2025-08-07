<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519Aegis256;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Filesystem\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class X25519Aegis256Test extends TestCase
{
    public const string MESSAGE = 'The Quick Brown Fox Jumps Over The Lazy Dog';

    public const string KNOWN_SENDER_KEYPAIR = 'kk72c6s2di5fKvBXLSbYCISOvj+a26p3nhe/+TzTi3osLpeqgv2ChN/RzsZskMYLU7jct02PprzdoHPeUwt5Kg==';

    public const string KNOWN_RECIPIENT_KEYPAIR = 'fvVzvZ085EQ+chb5HtMzBhLcBHjVAQi1g4CnQfuJnjTGPBGm6sIenWqy7v7b4iNdaQhtpn6gDVtpXquKyo7KKQ==';

    public const string ADDITIONAL_DATA = 'Some Random Metadata Not Sent in the Message';

    #[Test]
    public function encryptionHappyPath(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519Aegis256::encrypt($sender_keypair, $recipient_keypair->public, self::MESSAGE);
        $plaintext = X25519Aegis256::decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);

        // Assert the ciphertext is not the same as the plaintext
        self::assertNotSame(self::MESSAGE, $ciphertext);

        // Assert encrypting with the same message and key does not produce the same ciphertext
        self::assertNotSame($ciphertext, X25519Aegis256::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
        ));
    }

    #[Test]
    public function authenticatedEncryptionRegressionTest(): void
    {
        $sender_keypair = EncryptionKeyPair::import(self::KNOWN_SENDER_KEYPAIR);
        $recipient_keypair = EncryptionKeyPair::import(self::KNOWN_RECIPIENT_KEYPAIR);

        $ciphertext = CipherText::import(File::read(__DIR__ . '/../../Fixtures/lorem_X25519aegis256.txt'));

        $plaintext = X25519Aegis256::decrypt($recipient_keypair, $sender_keypair->public, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function anonymousEncryptionRegressionTest(): void
    {
        $recipient_keypair = EncryptionKeyPair::import(self::KNOWN_RECIPIENT_KEYPAIR);

        $ciphertext = CipherText::import(File::read(__DIR__ . '/../../Fixtures/lorem_X25519aegis256_anonymous.txt'));

        $plaintext = X25519Aegis256::unseal($recipient_keypair, $ciphertext);

        self::assertNotNull($plaintext);
        self::assertStringEqualsFile(__DIR__ . '/../../Fixtures/lorem.txt', $plaintext);
    }

    #[Test]
    public function anonymousEncryptionHappyPath(): void
    {
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519Aegis256::seal($recipient_keypair->public, self::MESSAGE);
        $plaintext = X25519Aegis256::unseal($recipient_keypair, $ciphertext);

        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function aeadHappyPath(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519Aegis256::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
            self::ADDITIONAL_DATA,
        );

        $plaintext = X25519Aegis256::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
            self::ADDITIONAL_DATA,
        );

        // Assert the decrypted message matches the original message
        self::assertSame(self::MESSAGE, $plaintext);
    }

    #[Test]
    public function aeadMissingOnEncryption(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519Aegis256::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
        );

        $plaintext = X25519Aegis256::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
            self::ADDITIONAL_DATA,
        );

        self::assertNull($plaintext);
    }

    #[Test]
    public function aeadMissingOnDecryption(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519Aegis256::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
            self::ADDITIONAL_DATA,
        );

        $plaintext = X25519Aegis256::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
        );

        self::assertNull($plaintext);
    }

    #[Test]
    public function aeadDoesNotMatch(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = X25519Aegis256::encrypt(
            $sender_keypair,
            $recipient_keypair->public,
            self::MESSAGE,
            self::ADDITIONAL_DATA,
        );

        $plaintext = X25519Aegis256::decrypt(
            $recipient_keypair,
            $sender_keypair->public,
            $ciphertext,
            "Some Other Metadata",
        );

        self::assertNull($plaintext);
    }
}
