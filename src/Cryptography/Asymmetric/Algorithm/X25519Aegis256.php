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
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\SealedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\Aegis256;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;

/**
 * Diffie-Hellman key exchange over Curve25519 + AEGIS-256 AEAD
 *
 * @see Aegis256 for more information on the encryption algorithm details
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Asymmetric::class)]
final readonly class X25519Aegis256 implements AsymmetricEncryptionAlgorithm
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
        Aegis256::available() || throw new CryptographicLogicException(
            'AEGIS-256 is not available (requires libsodium >= 1.0.19)',
        );

        $encrypted_message = Aegis256::encrypt(
            KeyExchange::encryption($key_pair, $public_key),
            $plaintext,
            $additional_data,
        );

        return new EncryptedMessageBox(
            AsymmetricAlgorithm::X25519Aegis256,
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
        Aegis256::available() || throw new CryptographicLogicException(
            'AEGIS-256 is not available (requires libsodium >= 1.0.19)',
        );

        return Aegis256::decrypt(
            KeyExchange::decryption($key_pair, $public_key),
            $ciphertext,
            $additional_data,
        );
    }

    public static function seal(
        #[\SensitiveParameter] EncryptionPublicKey $public_key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): SealedMessageBox {
        Aegis256::available() || throw new CryptographicLogicException(
            'AEGIS-256 is not available (requires libsodium >= 1.0.19)',
        );

        $key_pair = EncryptionKeyPair::generate();
        $nonce = new Nonce(\sodium_crypto_generichash($key_pair->public->bytes() . $public_key->bytes(), length: Aegis256::NONCE_BYTES));
        $ciphertext = new Ciphertext(\sodium_crypto_aead_aegis256_encrypt(
            $plaintext,
            $additional_data,
            $nonce->bytes(),
            KeyExchange::encryption($key_pair, $public_key)->bytes(),
        ));

        return new SealedMessageBox(
            AsymmetricAlgorithm::X25519Aegis256,
            $key_pair->public,
            $public_key,
            $ciphertext,
        );
    }

    public static function unseal(
        #[\SensitiveParameter] EncryptionKeyPair $key_pair,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        Aegis256::available() || throw new CryptographicLogicException(
            'AEGIS-256 is not available (requires libsodium >= 1.0.19)',
        );

        if ($ciphertext->length() < self::PUBLIC_KEY_BYTES) {
            return null;
        }

        $public_key = new EncryptionPublicKey(\substr($ciphertext->bytes(), 0, \SODIUM_CRYPTO_KX_PUBLICKEYBYTES));
        $ciphertext = new Ciphertext(\substr($ciphertext->bytes(), \SODIUM_CRYPTO_KX_PUBLICKEYBYTES));
        $nonce = new Nonce(\sodium_crypto_generichash($public_key->bytes() . $key_pair->public->bytes(), length: Aegis256::NONCE_BYTES));

        $plaintext = \sodium_crypto_aead_aegis256_decrypt(
            $ciphertext->bytes(),
            $additional_data,
            $nonce->bytes(),
            KeyExchange::decryption($key_pair, $public_key)->bytes(),
        );

        return $plaintext !== false ? $plaintext : null;
    }
}
