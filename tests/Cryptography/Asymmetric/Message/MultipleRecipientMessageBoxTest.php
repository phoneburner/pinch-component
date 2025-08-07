<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Asymmetric\Message;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\EncryptedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\MultipleRecipientMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PhoneBurner\Pinch\Exception\NotImplemented;
use PhoneBurner\Pinch\String\Encoding\Json;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MultipleRecipientMessageBoxTest extends TestCase
{
    private AsymmetricAlgorithm $algorithm;

    private EncryptionPublicKey $sender_key;

    private EncryptionPublicKey $recipient_key;

    private Ciphertext $ciphertext;

    private Nonce $nonce;

    protected function setUp(): void
    {
        $this->algorithm = AsymmetricAlgorithm::X25519Aegis256;
        $this->sender_key = EncryptionKeyPair::generate()->public();
        $this->recipient_key = EncryptionKeyPair::generate()->public();
        $this->ciphertext = new Ciphertext(\random_bytes(2048));
        $this->nonce = Nonce::generate();
    }

    #[Test]
    public function throwsWhenEncapsulatedKeyAlgorithmMismatches(): void
    {
        $this->expectException(CryptographicLogicException::class);
        $this->expectExceptionMessage('Encapsulated shared key algorithm must match the asymmetric algorithm');

        $encrypted_message = new EncryptedMessage(
            SymmetricAlgorithm::Aegis256,
            $this->ciphertext,
            $this->nonce,
        );

        $encapsulated_key = new EncryptedMessageBox(
            AsymmetricAlgorithm::X25519XChaCha20Poly1305,
            $this->sender_key,
            $this->recipient_key,
            $this->ciphertext,
            $this->nonce,
        );

        new MultipleRecipientMessageBox(
            $this->algorithm,
            $this->sender_key,
            [$encapsulated_key],
            $encrypted_message,
        );
    }

    #[Test]
    public function throwsWhenEncapsulatedKeySenderMismatches(): void
    {
        $this->expectException(CryptographicLogicException::class);
        $this->expectExceptionMessage('Encapsulated shared key sender must match the message sender');

        $encrypted_message = new EncryptedMessage(
            SymmetricAlgorithm::Aegis256,
            $this->ciphertext,
            $this->nonce,
        );

        $different_sender_key = EncryptionKeyPair::generate()->public();

        $encapsulated_key = new EncryptedMessageBox(
            $this->algorithm,
            $different_sender_key,
            $this->recipient_key,
            $this->ciphertext,
            $this->nonce,
        );

        new MultipleRecipientMessageBox(
            $this->algorithm,
            $this->sender_key,
            [$encapsulated_key],
            $encrypted_message,
        );
    }

    #[Test]
    public function jsonSerializeReturnsExpectedFormat(): void
    {
        $message_box = $this->createValidMessageBox();

        $json = $message_box->jsonSerialize();
        $data = Json::decode($json);

        self::assertSame(1, $data['v']);
        self::assertSame('X25519Aegis256', $data['alg']);
        self::assertSame($this->sender_key->export(), $data['pub']);
        self::assertCount(1, $data['k']);
        self::assertSame($this->recipient_key->export(), $data['k'][0]['pub']);
        self::assertSame($this->ciphertext->export(), $data['k'][0]['box']);
        self::assertSame('Aegis256', $data['m']['alg']);
        self::assertSame($this->nonce->export(), $data['m']['n']);
    }

    #[Test]
    public function bytesReturnsSerializedString(): void
    {
        $message_box = $this->createValidMessageBox();

        $this->expectException(NotImplemented::class);
        $message_box->bytes();
    }

    #[Test]
    public function lengthReturnsCorrectByteCount(): void
    {
        $message_box = $this->createValidMessageBox();

        $this->expectException(NotImplemented::class);
        $message_box->length();
    }

    #[Test]
    public function toStringReturnsSerializedString(): void
    {
        $message_box = $this->createValidMessageBox();

        $this->expectException(NotImplemented::class);
        echo (string)$message_box;
    }

    private function createValidMessageBox(): MultipleRecipientMessageBox
    {
        $encapsulated_key = new EncryptedMessageBox(
            $this->algorithm,
            $this->sender_key,
            $this->recipient_key,
            $this->ciphertext,
            $this->nonce,
        );

        return new MultipleRecipientMessageBox(
            $this->algorithm,
            $this->sender_key,
            [$encapsulated_key],
            new EncryptedMessage(
                SymmetricAlgorithm::Aegis256,
                $this->ciphertext,
                $this->nonce,
            ),
        );
    }
}
