<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Symmetric;

use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EncryptedMessage::class)]
final class EncryptedMessageTest extends TestCase
{
    private SymmetricAlgorithm $algorithm;
    private Ciphertext $ciphertext;
    private Nonce $nonce;
    private EncryptedMessage $message;

    protected function setUp(): void
    {
        $this->algorithm = SymmetricAlgorithm::XChaCha20Poly1305;
        $this->ciphertext = new Ciphertext(\random_bytes(128));
        $this->nonce = Nonce::generate();

        $this->message = new EncryptedMessage(
            $this->algorithm,
            $this->ciphertext,
            $this->nonce,
        );
    }

    #[Test]
    public function bytesReturnsConcatenatedNonceAndCiphertext(): void
    {
        $expected_bytes = $this->nonce->bytes() . $this->ciphertext->bytes();
        self::assertSame($expected_bytes, $this->message->bytes());
    }

    #[Test]
    public function lengthReturnsCombinedLength(): void
    {
        $expected_length = $this->nonce->length() + $this->ciphertext->length();
        self::assertSame($expected_length, $this->message->length());
    }

    #[Test]
    public function jsonSerializeReturnsBase64urlExport(): void
    {
        $expected_export = $this->message->export(EncryptedMessage::DEFAULT_ENCODING);
        self::assertSame($expected_export, $this->message->jsonSerialize());
    }

    #[Test]
    public function toStringReturnsBase64urlExport(): void
    {
        $expected_export = $this->message->export(EncryptedMessage::DEFAULT_ENCODING);
        self::assertSame($expected_export, (string)$this->message);
    }

    #[Test]
    public function canBeSerializedAndUnserialized(): void
    {
        $serialized = \serialize($this->message);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(EncryptedMessage::class, $unserialized);
        self::assertEquals($this->message, $unserialized);

        // Explicitly check properties
        self::assertSame($this->algorithm, $unserialized->algorithm);
        self::assertEquals($this->ciphertext, $unserialized->ciphertext);
        self::assertEquals($this->nonce, $unserialized->nonce);
    }
}
