<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\EncryptedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\SealedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidSignature;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\MessageSignature;

use function PhoneBurner\Pinch\String\bytes;

class Asymmetric
{
    public function encrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] \Stringable|string $plaintext,
        #[\SensitiveParameter] \Stringable|string $additional_data = '',
        AsymmetricAlgorithm $algorithm = AsymmetricAlgorithm::X25519Aegis256,
    ): EncryptedMessageBox {
        return $algorithm->implementation()::encrypt(
            $key_pair,
            $public_key,
            bytes($plaintext),
            (string)$additional_data,
        );
    }

    public function decrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] EncryptedMessageBox|Ciphertext $ciphertext,
        #[\SensitiveParameter] \Stringable|string $additional_data = '',
        AsymmetricAlgorithm $algorithm = AsymmetricAlgorithm::X25519Aegis256,
    ): string|null {
        return $algorithm->implementation()::decrypt(
            $key_pair,
            $public_key,
            $ciphertext,
            (string)$additional_data,
        );
    }

    /**
     * Anonymous Asymmetric Encryption
     *
     * Encrypt a string with the recipient's public key, so that only the recipient
     * can decrypt it with their private key.
     */
    public function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] \Stringable|string $plaintext,
        AsymmetricAlgorithm $algorithm = AsymmetricAlgorithm::X25519Aegis256,
    ): SealedMessageBox {
        return $algorithm->implementation()::seal($public_key, bytes($plaintext));
    }

    /**
     * Anonymous Asymmetric Encryption
     * Decrypt an encrypted string using the secret key.
     */
    public function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] Ciphertext $ciphertext,
        AsymmetricAlgorithm $algorithm = AsymmetricAlgorithm::X25519Aegis256,
    ): string|null {
        return $algorithm->implementation()::unseal($key_pair, $ciphertext);
    }

    /**
     * Create a detached Ed25519 digital signature for a message.
     */
    public function sign(
        #[\SensitiveParameter] SignatureKeyPair $key_pair,
        #[\SensitiveParameter] \Stringable|string $plaintext,
    ): MessageSignature {
        return new MessageSignature(\sodium_crypto_sign_detached(
            bytes($plaintext),
            $key_pair->secret->bytes(),
        ));
    }

    /**
     * Verify a detached Ed25519 digital signature for a message.
     */
    public function verify(
        #[\SensitiveParameter] SignaturePublicKey $public_key,
        #[\SensitiveParameter] MessageSignature $signature,
        #[\SensitiveParameter] \Stringable|string $plaintext,
    ): bool {
        return \sodium_crypto_sign_verify_detached(
            $signature->bytes() ?: throw new InvalidSignature('Signature Cannot Be Empty'),
            bytes($plaintext),
            $public_key->bytes(),
        );
    }
}
