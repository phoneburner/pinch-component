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
 * Symmetric Encryption: AES-256-GCM AEAD
 *
 * AES-256-GCM is a AEAD construction based on the AES-256 block cipher in
 * Galois/Counter Mode. It is a widely supported and secure symmetric encryption,
 * and when hardware-accelerated, can be very fast, though not as fast as
 * AEGIS-256. It's included in the Pinch framework for compatibility with
 * external applications that use this algorithm.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class Aes256Gcm implements SymmetricEncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES;
    public const int NONCE_BYTES = \SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessage {
        self::available() || throw new CryptographicLogicException(
            'AES-256-GCM is not available on this system',
        );

        $nonce = Nonce::generate(self::NONCE_BYTES);
        $ciphertext = new Ciphertext(\sodium_crypto_aead_aes256gcm_encrypt(
            $plaintext,
            $additional_data,
            $nonce->bytes(),
            $key->bytes(),
        ));

        return new EncryptedMessage(SymmetricAlgorithm::Aes256Gcm, $ciphertext, $nonce);
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::available() || throw new CryptographicLogicException(
            'AES-256-GCM is not available on this system',
        );

        if ($ciphertext->length() <= self::NONCE_BYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_aead_aes256gcm_decrypt(
            \substr($ciphertext->bytes(), self::NONCE_BYTES),
            $additional_data,
            \substr($ciphertext->bytes(), 0, self::NONCE_BYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }

    public static function available(): bool
    {
        return \sodium_crypto_aead_aes256gcm_is_available();
    }
}
