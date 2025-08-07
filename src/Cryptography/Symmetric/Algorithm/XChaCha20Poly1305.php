<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm;

use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Symmetric;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricEncryptionAlgorithm;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;

/**
 * Symmetric Encryption: XChaCha20-Poly1305 IETF AEAD
 *
 * The extended-nonce, IETF variant of the ChaCha20 cipher paired with a Poly1305
 * message authentication tag was the preferred symmetric encryption algorithm
 * prior to the introduction of the AEGIS-256 construction, and concerns over
 * key-commitment and partitioning attacks. It is still considered secure and is
 * implemented here for interoperability with external applications that use this
 * algorithm. However, where possible, the AEGIS-256 or XChaCha20-Blake2b
 * construction, which uses the same cipher but with split encryption/authentication
 * keys and Blake2b MAC instead of Poly1305, should be used instead.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class XChaCha20Poly1305 implements SymmetricEncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
    public const int NONCE_BYTES = \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessage {
        $nonce = Nonce::generate(self::NONCE_BYTES);
        $ciphertext = new Ciphertext(\sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $plaintext,
            $additional_data,
            $nonce->bytes(),
            $key->bytes(),
        ));

        return new EncryptedMessage(SymmetricAlgorithm::XChaCha20Poly1305, $ciphertext, $nonce);
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        if ($ciphertext->length() <= self::NONCE_BYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            \substr($ciphertext->bytes(), self::NONCE_BYTES),
            $additional_data,
            \substr($ciphertext->bytes(), 0, self::NONCE_BYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }
}
