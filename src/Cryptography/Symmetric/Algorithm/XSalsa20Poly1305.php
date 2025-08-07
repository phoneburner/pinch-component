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
 * Symmetric Encryption: XSalsa20-Poly1305
 *
 * The XSalsa20-Poly1305 authenticated encryption algorithm using the sodium
 * \sodium_crypto_secretbox() function. We're implementing this algorithm for
 * backwards compatibility and interoperability with external applications that
 * use this algorithm; however, we always want to use one of the AEAD constructions
 * like AEGIS-256 or XChaCha20-Blake2b, even if we don't use the additional data
 * field.
 *
 * Note: This is not a AEAD construction, and passing additional data will result
 * in an exception being thrown. Since this is the sole implementation of a non-AEAD
 * symmetric encryption algorithm, and is only intended for fallback usage in
 * specific cases, there is no separate interface for it.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class XSalsa20Poly1305 implements SymmetricEncryptionAlgorithm
{
    public const int KEY_BYTES = \SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
    public const int NONCE_BYTES = \SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessage {
        self::assertAssociatedDataLength($additional_data);
        $nonce = Nonce::generate(self::NONCE_BYTES);
        $ciphertext = new Ciphertext(\sodium_crypto_secretbox(
            $plaintext,
            $nonce->bytes(),
            $key->bytes(),
        ));

        return new EncryptedMessage(SymmetricAlgorithm::XSalsa20Poly1305, $ciphertext, $nonce);
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::assertAssociatedDataLength($additional_data);
        if ($ciphertext->length() <= self::NONCE_BYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_secretbox_open(
            \substr($ciphertext->bytes(), self::NONCE_BYTES),
            \substr($ciphertext->bytes(), 0, self::NONCE_BYTES),
            $key->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }

    private static function assertAssociatedDataLength(string $additional_data): void
    {
        $additional_data === '' || throw new CryptographicLogicException(
            'XSalsa20-Poly1305 is not an AEAD Construction',
        );
    }
}
