<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm;

use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Symmetric;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricEncryptionAlgorithm;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;

/**
 * Symmetric Encryption: AEGIS-256 AEAD
 *
 * - 256-bit key, 256-bit nonce, 256-bit authentication tag
 * - Considered to be key-commiting due to the size of the key, nonce, and tag
 * - MAC size ensures collision resistance for a given key, allowing it to be used
 *   as a unique identifier for message
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class Aegis256 implements SymmetricEncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_AEAD_AEGIS256_KEYBYTES;
    public const int NONCE_BYTES = \SODIUM_CRYPTO_AEAD_AEGIS256_NPUBBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessage {
        self::available() || throw new CryptographicLogicException(
            'AEGIS-256 is not available (requires libsodium >= 1.0.19)',
        );

        $nonce = Nonce::generate(self::NONCE_BYTES);
        $ciphertext = new Ciphertext(\sodium_crypto_aead_aegis256_encrypt(
            $plaintext,
            $additional_data,
            $nonce->bytes(),
            $key->bytes(),
        ));

        return new EncryptedMessage(SymmetricAlgorithm::Aegis256, $ciphertext, $nonce);
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::available() || throw new CryptographicLogicException(
            'AEGIS-256 is not available (requires libsodium >= 1.0.19)',
        );

        if ($ciphertext->length() <= self::NONCE_BYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_aead_aegis256_decrypt(
            \substr($ciphertext->bytes(), self::NONCE_BYTES),
            $additional_data,
            \substr($ciphertext->bytes(), 0, self::NONCE_BYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }

    public static function available(): bool
    {
        return \function_exists('sodium_crypto_aead_aegis256_keygen');
    }
}
