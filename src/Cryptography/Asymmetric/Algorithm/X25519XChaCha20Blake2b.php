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
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyDerivation;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\XChaCha20Blake2b;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;

/**
 * Diffie-Hellman key exchange over Curve25519 + XChaCha20 + Blake2b AEAD
 *
 * @see XChaCha20Blake2b for more information on the encryption algorithm details
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Asymmetric::class)]
final readonly class X25519XChaCha20Blake2b implements AsymmetricEncryptionAlgorithm
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
        $encrypted_message = XChaCha20Blake2b::encrypt(
            KeyExchange::encryption($key_pair, $public_key),
            $plaintext,
            $additional_data,
        );

        return new EncryptedMessageBox(
            AsymmetricAlgorithm::X25519XChaCha20Blake2b,
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
        return XChaCha20Blake2b::decrypt(
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
        $key_pair = EncryptionKeyPair::generate();
        $nonce = new Nonce(\sodium_crypto_generichash($key_pair->public->bytes() . $public_key->bytes(), length: XChaCha20Blake2b::NONCE_BYTES));
        $key = KeyExchange::encryption($key_pair, $public_key);

        $hkdf_salt = \substr($nonce->bytes(), 0, XChaCha20Blake2b::HKDF_SALT_BYTES);
        $xor_nonce = \substr($nonce->bytes(), XChaCha20Blake2b::HKDF_SALT_BYTES);

        // HKDF-Extract & HKDF-Expand
        $encryption_key = KeyDerivation::hkdf($key, info: XChaCha20Blake2b::HKDF_SBOX_INFO . $hkdf_salt);
        $authentication_key = KeyDerivation::hkdf($key, info: XChaCha20Blake2b::HKDF_AUTH_INFO . $hkdf_salt);

        // Encrypt the plaintext message using the derived encryption key and random nonce
        $encrypted_text = \sodium_crypto_stream_xchacha20_xor($plaintext, $xor_nonce, $encryption_key);
        \sodium_memzero($encryption_key);

        // Calculate a 256-bit authentication tag using Pre-Authentication Encoding (PAE)
        $pae = XChaCha20Blake2b::pae($hkdf_salt, $xor_nonce, $additional_data, $encrypted_text);
        $authentication_tag = \sodium_crypto_generichash($pae, $authentication_key, XChaCha20Blake2b::AUTH_TAG_BYTES);
        \sodium_memzero($pae);
        \sodium_memzero($authentication_key);

        // Append the authentication tag to the encrypted text to create the ciphertext
        $ciphertext = new Ciphertext($encrypted_text . $authentication_tag);

        \sodium_memzero($hkdf_salt);
        \sodium_memzero($xor_nonce);
        \sodium_memzero($encrypted_text);
        \sodium_memzero($authentication_tag);

        return new SealedMessageBox(
            AsymmetricAlgorithm::X25519XChaCha20Blake2b,
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
        if ($ciphertext->length() < self::PUBLIC_KEY_BYTES) {
            return null;
        }

        $public_key = new EncryptionPublicKey(\substr($ciphertext->bytes(), 0, self::PUBLIC_KEY_BYTES));
        $key = KeyExchange::decryption($key_pair, $public_key);

        // Unpack the nonce into the component parts
        $nonce = new Nonce(\sodium_crypto_generichash($public_key->bytes() . $key_pair->public->bytes(), length: XChaCha20Blake2b::NONCE_BYTES));
        $hkdf_salt = \substr($nonce->bytes(), 0, XChaCha20Blake2b::HKDF_SALT_BYTES);
        $xor_nonce = \substr($nonce->bytes(), XChaCha20Blake2b::HKDF_SALT_BYTES);

        // Unpack the ciphertext into the component parts
        $ciphertext = new Ciphertext(\substr($ciphertext->bytes(), self::PUBLIC_KEY_BYTES));
        $encrypted_text = \substr($ciphertext->bytes(), 0, -XChaCha20Blake2b::AUTH_TAG_BYTES,);
        $authentication_tag = \substr($ciphertext->bytes(), -XChaCha20Blake2b::AUTH_TAG_BYTES);

        // HKDF-Extract & HKDF-Expand
        $encryption_key = KeyDerivation::hkdf($key, info: XChaCha20Blake2b::HKDF_SBOX_INFO . $hkdf_salt);
        $authentication_key = KeyDerivation::hkdf($key, info: XChaCha20Blake2b::HKDF_AUTH_INFO . $hkdf_salt);

        // Verify the authentication tag before decrypting the message by
        // calculating the expected tag with the derived authentication key.
        $pae = XChaCha20Blake2b::pae($hkdf_salt, $xor_nonce, $additional_data, $encrypted_text);
        $calculated_tag = \sodium_crypto_generichash($pae, $authentication_key, XChaCha20Blake2b::AUTH_TAG_BYTES);
        \sodium_memzero($authentication_key);
        \sodium_memzero($pae);
        \sodium_memzero($hkdf_salt);

        // If the authentication tag is valid, decrypt the message, using
        // the encryption key derived from the PRK key. Note that we use
        // hash_equals() here for constant-time comparison.
        $plaintext = \hash_equals($calculated_tag, $authentication_tag)
            ? \sodium_crypto_stream_xchacha20_xor($encrypted_text, $xor_nonce, $encryption_key)
            : null;

        // Clear the remaining sensitive data from memory
        \sodium_memzero($encryption_key);
        \sodium_memzero($xor_nonce);
        \sodium_memzero($encrypted_text);
        \sodium_memzero($calculated_tag);
        \sodium_memzero($authentication_tag);

        return $plaintext;
    }
}
