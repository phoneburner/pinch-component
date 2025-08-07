<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Exception\NotImplemented;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;

final readonly class MultipleRecipientMessageBox implements BinaryString
{
    use BinaryStringExportBehavior;

    /**
     * @param array<EncryptedMessageBox> $encapsulated_keys
     */
    public function __construct(
        public AsymmetricAlgorithm $asymmetric_algorithm,
        #[\SensitiveParameter] public EncryptionPublicKey $sender_public_key,
        #[\SensitiveParameter] public array $encapsulated_keys,
        #[\SensitiveParameter] public EncryptedMessage $encrypted_message,
    ) {
        foreach ($this->encapsulated_keys as $encrypted_shared_key) {
            if (! $encrypted_shared_key instanceof EncryptedMessageBox) {
                throw new \InvalidArgumentException();
            }

            if ($encrypted_shared_key->algorithm !== $asymmetric_algorithm) {
                throw new CryptographicLogicException('Encapsulated shared key algorithm must match the asymmetric algorithm');
            }

            if ($encrypted_shared_key->sender_public_key != $sender_public_key) {
                throw new CryptographicLogicException('Encapsulated shared key sender must match the message sender');
            }
        }
    }

    public function bytes(): string
    {
        throw new NotImplemented();
    }

    public function length(): int
    {
        throw new NotImplemented();
    }

    public function __toString(): string
    {
        throw new NotImplemented();
    }

    public function jsonSerialize(): string
    {
        $keys = [];
        foreach ($this->encapsulated_keys as $encapsulated_key) {
            $keys[] = [
                'pub' => $encapsulated_key->recipient_public_key->export(),
                'box' => $encapsulated_key->ciphertext->export(),
            ];
        }

        $envelope = [
            'v' => 1,
            'alg' => $this->asymmetric_algorithm->name,
            'pub' => $this->sender_public_key->export(),
            'k' => $keys,
            'm' => [
                'alg' => $this->encrypted_message->algorithm->name,
                'n' => $this->encrypted_message->nonce->export(),
                'c' => $this->encrypted_message->ciphertext->export(),
            ],
        ];

        return \json_encode($envelope, \JSON_THROW_ON_ERROR);
    }
}
