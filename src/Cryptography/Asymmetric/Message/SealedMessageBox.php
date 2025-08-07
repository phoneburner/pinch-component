<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * A sealed message box is designed to anonymously encrypt a message for a recipient.
 * While the recipient can decrypt the message, they cannot verify the identity
 * of the sender. (Note, for most use-cases, you should use regular, authenticated
 * asymmetric encryption instead.) The encryption/decryption process is almost
 * identical to regular asymmetric encryption, but a new ephemeral key pair is
 * generated for each message, and the secret key component is discarded immediately
 * after encryption.
 *
 * Following the NaCl/Sodium "crypto_box" pattern, the nonce used for encryption
 * is a hash of the ephemeral public key and the recipient public key.
 */
final readonly class SealedMessageBox implements BinaryString
{
    use BinaryStringExportBehavior;

    public const Encoding DEFAULT_ENCODING = Encoding::Base64Url;

    public function __construct(
        public AsymmetricAlgorithm $algorithm,
        #[\SensitiveParameter] public EncryptionPublicKey $ephemeral_public_key,
        #[\SensitiveParameter] public EncryptionPublicKey $recipient_public_key,
        #[\SensitiveParameter] public Ciphertext $ciphertext,
    ) {
    }

    public function bytes(): string
    {
        return $this->ephemeral_public_key->bytes() . $this->ciphertext->bytes();
    }

    public function length(): int
    {
        return $this->ephemeral_public_key->length() + $this->ciphertext->length();
    }

    public function jsonSerialize(): string
    {
        return $this->export(self::DEFAULT_ENCODING);
    }

    public function __toString(): string
    {
        return $this->export(self::DEFAULT_ENCODING);
    }

    public function __serialize(): array
    {
        return [
            $this->algorithm->name,
            $this->ephemeral_public_key->export(self::DEFAULT_ENCODING),
            $this->recipient_public_key->export(self::DEFAULT_ENCODING),
            $this->ciphertext->export(self::DEFAULT_ENCODING),
        ];
    }

    /**
     * @param list<string> $data
     */
    public function __unserialize(array $data): void
    {
        $this->__construct(
            AsymmetricAlgorithm::{$data[0]},
            EncryptionPublicKey::import($data[1], self::DEFAULT_ENCODING),
            EncryptionPublicKey::import($data[2], self::DEFAULT_ENCODING),
            Ciphertext::import($data[3], self::DEFAULT_ENCODING),
        );
    }
}
