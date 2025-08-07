<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricEncryptionAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\KeyExchange;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\EncryptedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Exception\UnsupportedOperation;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\Aes256Gcm;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;

/**
 * Diffie-Hellman key exchange over Curve25519 + AES-256-GCM AEAD
 *
 * @see Aes256Gcm for more information on the encryption algorithm details
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Asymmetric::class)]
final readonly class X25519Aes256Gcm implements AsymmetricEncryptionAlgorithm
{
    use HasCommonAnonymousEncryptionBehavior;

    public const int KEY_PAIR_BYTES = \SODIUM_CRYPTO_KX_KEYPAIRBYTES; // 64 bytes
    public const int PUBLIC_KEY_BYTES = \SODIUM_CRYPTO_KX_PUBLICKEYBYTES; // 32 bytes
    public const int SECRET_KEY_BYTES = \SODIUM_CRYPTO_KX_SECRETKEYBYTES; // 32 bytes

    public static function encrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessageBox {
        Aes256Gcm::available() || throw new CryptographicLogicException(
            'AES-256-GCM is not available on this system',
        );

        $encrypted_message = Aes256Gcm::encrypt(
            KeyExchange::encryption($key_pair, $public_key),
            $plaintext,
            $additional_data,
        );

        return new EncryptedMessageBox(
            AsymmetricAlgorithm::X25519Aes256Gcm,
            $key_pair->public,
            $public_key,
            $encrypted_message->ciphertext,
            $encrypted_message->nonce,
        );
    }

    public static function decrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        Aes256Gcm::available() || throw new CryptographicLogicException(
            'AES-256-GCM is not available on this system',
        );

        return Aes256Gcm::decrypt(
            KeyExchange::decryption($key_pair, $public_key),
            $ciphertext,
            $additional_data,
        );
    }

    public static function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): never {
        throw new UnsupportedOperation('Sealing is not supported with AES-256-GCM (Weak Nonce Length)');
    }

    public static function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): never {
        throw new UnsupportedOperation('Sealing is not supported with AES-256-GCM (Weak Nonce Length)');
    }
}
