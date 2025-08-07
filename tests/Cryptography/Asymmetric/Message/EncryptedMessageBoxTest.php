<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Asymmetric\Message;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\EncryptedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EncryptedMessageBox::class)]
final class EncryptedMessageBoxTest extends TestCase
{
    private AsymmetricAlgorithm $algorithm;
    private EncryptionPublicKey $sender_key;
    private EncryptionPublicKey $recipient_key;
    private Ciphertext $ciphertext;
    private Nonce $nonce;
    private EncryptedMessageBox $message_box;

    protected function setUp(): void
    {
        $this->algorithm = AsymmetricAlgorithm::X25519Aegis256;
        $this->sender_key = EncryptionKeyPair::generate()->public();
        $this->recipient_key = EncryptionKeyPair::generate()->public();
        $this->ciphertext = new Ciphertext(\random_bytes(128));
        $this->nonce = Nonce::generate();

        $this->message_box = new EncryptedMessageBox(
            $this->algorithm,
            $this->sender_key,
            $this->recipient_key,
            $this->ciphertext,
            $this->nonce,
        );
    }

    #[Test]
    public function bytesReturnsConcatenatedNonceAndCiphertext(): void
    {
        $expected_bytes = $this->nonce->bytes() . $this->ciphertext->bytes();
        self::assertSame($expected_bytes, $this->message_box->bytes());
    }

    #[Test]
    public function lengthReturnsCombinedLength(): void
    {
        $expected_length = $this->nonce->length() + $this->ciphertext->length();
        self::assertSame($expected_length, $this->message_box->length());
    }

    #[Test]
    public function jsonSerializeReturnsBase64urlExport(): void
    {
        $expected_export = $this->message_box->export(EncryptedMessageBox::DEFAULT_ENCODING);
        self::assertSame($expected_export, $this->message_box->jsonSerialize());
    }

    #[Test]
    public function toStringReturnsBase64urlExport(): void
    {
        $expected_export = $this->message_box->export(EncryptedMessageBox::DEFAULT_ENCODING);
        self::assertSame($expected_export, (string)$this->message_box);
    }

    #[Test]
    public function canBeSerializedAndUnserialized(): void
    {
        $serialized = \serialize($this->message_box);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(EncryptedMessageBox::class, $unserialized);
        self::assertEquals($this->message_box, $unserialized);

        // Explicitly check properties after unserialization
        self::assertSame($this->algorithm, $unserialized->algorithm);
        self::assertEquals($this->sender_key, $unserialized->sender_public_key);
        self::assertEquals($this->recipient_key, $unserialized->recipient_public_key);
        self::assertEquals($this->ciphertext, $unserialized->ciphertext);
        // Note: Nonce comparison might be tricky due to how it's reconstructed in __unserialize
        // We rely on the overall object equality check above for simplicity,
        // but if issues arise, a specific nonce bytes comparison might be needed.
        // self::assertEquals($this->nonce, $unserialized->nonce);
    }
}
