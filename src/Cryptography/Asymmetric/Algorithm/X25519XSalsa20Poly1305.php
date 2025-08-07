<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm;

use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricEncryptionAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\EncryptedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\SealedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;

/**
 * Implements the X25519-XSalsa20-Poly1305 encryption algorithm using the default
 * libsodium implementations, e.g. \sodium_crypto_box() and \sodium_crypto_box_seal().
 *
 * @see XSalsa20Poly1305 for more information on the encryption algorithm details
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Asymmetric::class)]
final readonly class X25519XSalsa20Poly1305 implements AsymmetricEncryptionAlgorithm
{
    public const int KEY_PAIR_BYTES = \SODIUM_CRYPTO_BOX_KEYPAIRBYTES; // 64 bytes
    public const int PUBLIC_KEY_BYTES = \SODIUM_CRYPTO_BOX_PUBLICKEYBYTES; // 32 bytes
    public const int SECRET_KEY_BYTES = \SODIUM_CRYPTO_BOX_SECRETKEYBYTES; // 32 bytes

    public static function encrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessageBox {
        self::assertAssociatedDataLength($additional_data);

        // The \sodium_crypto_box() function consumes a single key pair
        // value made up of the recipient's public key and the sender's secret key.
        $box_key_pair = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
            \sodium_crypto_box_secretkey($key_pair->bytes()),
            $public_key->bytes(),
        );

        $nonce = Nonce::generate(\SODIUM_CRYPTO_BOX_NONCEBYTES);
        $ciphertext = \sodium_crypto_box($plaintext, $nonce->bytes(), $box_key_pair);

        \sodium_memzero($box_key_pair);

        return new EncryptedMessageBox(
            AsymmetricAlgorithm::X25519XSalsa20Poly1305,
            $key_pair->public,
            $public_key,
            new Ciphertext($ciphertext),
            $nonce,
        );
    }

    public static function decrypt(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::assertAssociatedDataLength($additional_data);

        // The \sodium_crypto_box_open() function consumes a single key pair
        // value made up of the recipient's secret key and the sender's public key.
        $box_key_pair = \sodium_crypto_box_keypair_from_secretkey_and_publickey(
            \sodium_crypto_box_secretkey($key_pair->bytes()),
            $public_key->bytes(),
        );

        $plaintext = \sodium_crypto_box_open(
            \substr($ciphertext->bytes(), \SODIUM_CRYPTO_BOX_NONCEBYTES),
            \substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_BOX_NONCEBYTES),
            $box_key_pair,
        );

        \sodium_memzero($box_key_pair);

        return $plaintext !== false ? $plaintext : null;
    }

    public static function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): SealedMessageBox {
        self::assertAssociatedDataLength($additional_data);
        $bytes = \sodium_crypto_box_seal($plaintext, $public_key->bytes());

        return new SealedMessageBox(
            AsymmetricAlgorithm::X25519XSalsa20Poly1305,
            new EncryptionPublicKey(\substr($bytes, 0, \SODIUM_CRYPTO_BOX_PUBLICKEYBYTES)),
            $public_key,
            new Ciphertext(\substr($bytes, \SODIUM_CRYPTO_BOX_PUBLICKEYBYTES)),
        );
    }

    public static function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        self::assertAssociatedDataLength($additional_data);
        if ($ciphertext->length() < \SODIUM_CRYPTO_BOX_NONCEBYTES) {
            return null;
        }

        $plaintext = \sodium_crypto_box_seal_open($ciphertext->bytes(), $key_pair->bytes());

        return $plaintext !== false ? $plaintext : null;
    }

    private static function assertAssociatedDataLength(string $additional_data): void
    {
        $additional_data === '' || throw new CryptographicLogicException(
            'X25519-XSalsa20-Poly1305 is not an AEAD Construction',
        );
    }
}
