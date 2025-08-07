<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;
use PhoneBurner\Pinch\String\Encoding\Encoding;

final readonly class EncryptedMessageBox implements BinaryString
{
    use BinaryStringExportBehavior;

    public const Encoding DEFAULT_ENCODING = Encoding::Base64Url;

    public function __construct(
        public AsymmetricAlgorithm $algorithm,
        #[\SensitiveParameter] public EncryptionPublicKey $sender_public_key,
        #[\SensitiveParameter] public EncryptionPublicKey $recipient_public_key,
        #[\SensitiveParameter] public Ciphertext $ciphertext,
        #[\SensitiveParameter] public Nonce $nonce,
    ) {
    }

    public function bytes(): string
    {
        return $this->nonce->bytes() . $this->ciphertext->bytes();
    }

    public function length(): int
    {
        return $this->nonce->length() + $this->ciphertext->length();
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
            $this->sender_public_key->export(self::DEFAULT_ENCODING),
            $this->recipient_public_key->export(self::DEFAULT_ENCODING),
            $this->ciphertext->export(self::DEFAULT_ENCODING),
            $this->nonce->export(self::DEFAULT_ENCODING),
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
            Nonce::import($data[4], self::DEFAULT_ENCODING),
        );
    }
}
