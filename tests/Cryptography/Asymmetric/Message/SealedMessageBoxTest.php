<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Asymmetric\Message;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\SealedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SealedMessageBox::class)]
final class SealedMessageBoxTest extends TestCase
{
    private AsymmetricAlgorithm $algorithm;
    private EncryptionPublicKey $ephemeral_key;
    private EncryptionPublicKey $recipient_key;
    private Ciphertext $ciphertext;
    private SealedMessageBox $message_box;

    protected function setUp(): void
    {
        $this->algorithm = AsymmetricAlgorithm::X25519Aegis256;
        // Use distinct keys for clarity
        $this->ephemeral_key = EncryptionKeyPair::generate()->public();
        $this->recipient_key = EncryptionKeyPair::generate()->public();
        $this->ciphertext = new Ciphertext(\random_bytes(256));

        $this->message_box = new SealedMessageBox(
            $this->algorithm,
            $this->ephemeral_key,
            $this->recipient_key,
            $this->ciphertext,
        );
    }

    #[Test]
    public function bytesReturnsConcatenatedKeyAndCiphertext(): void
    {
        $expected_bytes = $this->ephemeral_key->bytes() . $this->ciphertext->bytes();
        self::assertSame($expected_bytes, $this->message_box->bytes());
    }

    #[Test]
    public function lengthReturnsCombinedLength(): void
    {
        $expected_length = $this->ephemeral_key->length() + $this->ciphertext->length();
        self::assertSame($expected_length, $this->message_box->length());
    }

    #[Test]
    public function jsonSerializeReturnsBase64urlExport(): void
    {
        $expected_export = $this->message_box->export(SealedMessageBox::DEFAULT_ENCODING);
        self::assertSame($expected_export, $this->message_box->jsonSerialize());
    }

    #[Test]
    public function toStringReturnsBase64urlExport(): void
    {
        $expected_export = $this->message_box->export(SealedMessageBox::DEFAULT_ENCODING);
        self::assertSame($expected_export, (string)$this->message_box);
    }

    #[Test]
    public function canBeSerializedAndUnserialized(): void
    {
        $serialized = \serialize($this->message_box);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(SealedMessageBox::class, $unserialized);
        // Use assertEquals for object comparison
        self::assertEquals($this->message_box, $unserialized);

        // Explicitly check properties after unserialization
        self::assertSame($this->algorithm, $unserialized->algorithm);
        self::assertEquals($this->ephemeral_key, $unserialized->ephemeral_public_key);
        self::assertEquals($this->recipient_key, $unserialized->recipient_public_key);
        self::assertEquals($this->ciphertext, $unserialized->ciphertext);
    }
}
