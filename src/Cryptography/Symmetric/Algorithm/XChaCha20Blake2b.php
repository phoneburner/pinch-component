<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm;

use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyDerivation;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\Nonce;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Symmetric;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricEncryptionAlgorithm;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\PackFormat;

/**
 * Symmetric Encryption (AEAD): XChaCha20-BLAKE2b
 *
 * This is a modern approach that addresses several shortcomings
 *  around both the `\sodium_crypto_secretbox()` and \sodium_crypto_aead_*() APIs.
 *  Specifically, the algorithm below mitigates:
 *   - Chosen-Ciphertext Attacks
 *   - Key-Commitment aka "Invisible Salamander" attacks
 *   - Timing Attacks on the MAC
 *
 * HKDF-Extract & HKDF-Expand
 *  to make this construction more secure and key-commiting, we use a
 * salted derivative of HKDF using Blake2b instead of a true HMAC to split the
 * keys. First extract a pseduo-random key (PRK) by hashing the key with a null-byte
 * salt equal to the key-length. Then extract the derived keys for encryption and
 * authentication, concatenating the salt to the info/context parameter. Note that
 * because a single round of HKDF produces exactly the size key we need, we can
 * shortcut the step of extracting the subkeys from the PRK. Note that the "\x01"
 * in the HKDF-Expand step is the HKDF subkey block id.
 *
 * Notes:
 * - This can be executed on any PHP 7.2+ with the Sodium extension and does not
 *    rely on a specific version of libsodium or hardware support like AES-256-GCM
 *    or AEGIS-256.
 *
 * - Follows the best-practice "encrypt-then-MAC" pattern, where we do not try to
 *   decrypt a ciphertext that does not have a valid message authentication tag
 *   https://moxie.org/2011/12/13/the-cryptographic-doom-principle.html
 *
 * - Using a keyed BLAKE2b-MAC for authentication instead of Poly1305 makes the
 *   ciphertext "message commiting", and more resistant to "invisible salamander"
 *   attacks. (https://eprint.iacr.org/2020/1456)
 *
 * - This algorithm produces a ciphertext with 88 bytes of overhead: the 32-bit salt,
 *   24-bit nonce, and 32-bit authentication. This is only 32 bytes more than the
 *   other Sodium AEAD constructions; it's a small price to pay for the added
 *   security benefits of using a key-commiting AEAD construction.
 *
 * @link https://github.com/paragonie/halite/blob/master/src/Symmetric/Crypto.php
 * for the reference implementation, this is based on.
 */
#[Internal('Client Code Should Not Use Algorithm Implementation', Symmetric::class)]
final readonly class XChaCha20Blake2b implements SymmetricEncryptionAlgorithm
{
    /**
     * HKDF Info Parameters for Derived Keys (Encryption and Authentication)
     *
     * @see https://tools.ietf.org/html/rfc5869#section-3.2
     */
    public const string HKDF_SBOX_INFO = 'EncryptionKey';
    public const string HKDF_AUTH_INFO = 'AuthenticationKey';

    public const int KEY_BYTES = \SODIUM_CRYPTO_STREAM_XCHACHA20_KEYBYTES;
    public const int NONCE_BYTES = self::XOR_NONCE_BYTES + self::HKDF_SALT_BYTES;

    public const int XOR_NONCE_BYTES = \SODIUM_CRYPTO_STREAM_XCHACHA20_NONCEBYTES;
    public const int HKDF_SALT_BYTES = \SODIUM_CRYPTO_GENERICHASH_BYTES;
    public const int AUTH_TAG_BYTES = \SODIUM_CRYPTO_GENERICHASH_BYTES;
    public const int MIN_CIPHERTEXT_BYTES = self::NONCE_BYTES + self::AUTH_TAG_BYTES;

    public static function encrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] string $plaintext,
        #[\SensitiveParameter] string $additional_data = '',
    ): EncryptedMessage {
        // HKDF-Extract & HKDF-Expand
        $hkdf_salt = \random_bytes(self::HKDF_SALT_BYTES);
        $encryption_key = KeyDerivation::hkdf($key, info: self::HKDF_SBOX_INFO . $hkdf_salt);
        $authentication_key = KeyDerivation::hkdf($key, info: self::HKDF_AUTH_INFO . $hkdf_salt);

        // Encrypt the plaintext message using the derived encryption key and random nonce
        $xor_nonce = \random_bytes(self::XOR_NONCE_BYTES);
        $encrypted_text = \sodium_crypto_stream_xchacha20_xor($plaintext, $xor_nonce, $encryption_key);
        \sodium_memzero($encryption_key);

        // Calculate a 256-bit authentication tag using Pre-Authentication Encoding (PAE)
        $pae = self::pae($hkdf_salt, $xor_nonce, $additional_data, $encrypted_text);
        $authentication_tag = \sodium_crypto_generichash($pae, $authentication_key, self::AUTH_TAG_BYTES);
        \sodium_memzero($pae);
        \sodium_memzero($authentication_key);

        // concatenate the hkdf_salt and xor_nonce to create the "nonce"
        $nonce = new Nonce($hkdf_salt . $xor_nonce);

        // Append the authentication tag to the encrypted text to create the ciphertext
        $ciphertext = new Ciphertext($encrypted_text . $authentication_tag);

        \sodium_memzero($hkdf_salt);
        \sodium_memzero($xor_nonce);
        \sodium_memzero($encrypted_text);
        \sodium_memzero($authentication_tag);

        return new EncryptedMessage(SymmetricAlgorithm::XChaCha20Blake2b, $ciphertext, $nonce);
    }

    public static function decrypt(
        #[\SensitiveParameter] SharedKey $key,
        #[\SensitiveParameter] BinaryString $ciphertext,
        #[\SensitiveParameter] string $additional_data = '',
    ): string|null {
        if ($ciphertext->length() < self::MIN_CIPHERTEXT_BYTES) {
            return null;
        }

        // Unpack the ciphertext into the component parts
        $hkdf_salt = \substr($ciphertext->bytes(), 0, self::HKDF_SALT_BYTES);
        $xor_nonce = \substr($ciphertext->bytes(), self::HKDF_SALT_BYTES, self::XOR_NONCE_BYTES);
        $authentication_tag = \substr(
            $ciphertext->bytes(),
            $ciphertext->length() - self::AUTH_TAG_BYTES,
            self::AUTH_TAG_BYTES,
        );

        $encrypted_text = \substr(
            $ciphertext->bytes(),
            self::NONCE_BYTES,
            \strlen($ciphertext->bytes()) - self::MIN_CIPHERTEXT_BYTES,
        );

        // HKDF-Extract & HKDF-Expand
        $encryption_key = KeyDerivation::hkdf($key, info: self::HKDF_SBOX_INFO . $hkdf_salt);
        $authentication_key = KeyDerivation::hkdf($key, info: self::HKDF_AUTH_INFO . $hkdf_salt);

        // Verify the authentication tag before decrypting the message by
        // calculating the expected tag with the derived authentication key.
        $pae = self::pae($hkdf_salt, $xor_nonce, $additional_data, $encrypted_text);
        $calculated_tag = \sodium_crypto_generichash($pae, $authentication_key, self::AUTH_TAG_BYTES);
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

    /**
     * Pre-Authentication Encoding
     *
     * Before passing a message to a MAC function, the message must be encoded
     * in a specific way, to prevent certain types of attacks. This encoding is
     * called Pre-Authentication Encoding (PAE). The PAE string is constructed
     * as follows:
     * - The number of parts (as a 64-bit little-endian integer)
     * - For each part:
     *   - The length of the part (as a 64-bit little-endian integer)
     *   - The part itself
     *
     * The PAE string can then be passed to the MAC function as the message. This
     * makes it impossible for an attacker to create a collision with only a
     * partially controlled plaintext or creating an integer overflow.
     *
     * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Common.md#authentication-padding
     * @return non-empty-string
     **/
    public static function pae(string ...$parts): string
    {
        $accumulator = \pack(PackFormat::INT64_UNSIGNED_LE, \count($parts) & \PHP_INT_MAX);
        foreach ($parts as $string) {
            $accumulator .= \pack(PackFormat::INT64_UNSIGNED_LE, \strlen($string) & \PHP_INT_MAX);
            $accumulator .= $string;
        }

        return $accumulator ?: throw new \LogicException('Accumulator String Cannot Be Empty');
    }
}
