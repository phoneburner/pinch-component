<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoCryptoException;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoMessage;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoPurpose;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoVersion;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Cryptography\Util;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * Version 4: Sodium Modern
 * v4.local
 * v4.public
 *
 * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version4.md
 */
final class Version4 implements PasetoProtocol
{
    public const PasetoVersion VERSION = PasetoVersion::V4;
    public const string HEADER_PUBLIC = PasetoVersion::V4->value . '.public.';
    public const string HEADER_LOCAL = PasetoVersion::V4->value . '.local.';

    private const string HKDF_INFO_SBOX = 'paseto-encryption-key';
    private const string HKDF_INFO_AUTH = 'paseto-auth-key-for-aead';
    private const int KEY_BYTES = \SODIUM_CRYPTO_STREAM_XCHACHA20_KEYBYTES;
    private const int NONCE_BYTES = \SODIUM_CRYPTO_GENERICHASH_BYTES;
    private const int STREAM_NONCE_BYTES = \SODIUM_CRYPTO_STREAM_XCHACHA20_NONCEBYTES;

    public static function encrypt(
        SharedKey $key,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto {
        // Generate a random nonce value, we'll use it to do something similar to
        // HKDF-Extract and HKDF-Expand to derive the encryption key and
        // encryption nonce. We'll later use it again to derive the authentication
        // key. Note that this algorithm is an early version of what would later
        // become our XChaCha20Blake2b algorithm.
        $nonce = \random_bytes(self::NONCE_BYTES);

        $prk = \sodium_crypto_generichash(self::HKDF_INFO_SBOX . $nonce, $key->bytes(), self::KEY_BYTES + self::STREAM_NONCE_BYTES);
        $encryption_key = \substr($prk, 0, self::KEY_BYTES);
        $encryption_nonce = \substr($prk, self::KEY_BYTES, self::STREAM_NONCE_BYTES);
        $ciphertext = \sodium_crypto_stream_xchacha20_xor($message->payload, $encryption_nonce, $encryption_key);
        \sodium_memzero($prk);
        \sodium_memzero($encryption_key);
        \sodium_memzero($encryption_nonce);

        $authentication_key = \sodium_crypto_generichash(self::HKDF_INFO_AUTH . $nonce, $key->bytes(), self::KEY_BYTES);
        $pae = Util::pae(self::HEADER_LOCAL, $nonce, $ciphertext, $message->footer, $additional_data);
        $mac = \sodium_crypto_generichash($pae, $authentication_key, self::KEY_BYTES);
        \sodium_memzero($authentication_key);

        $token = self::HEADER_LOCAL . self::encode($nonce . $ciphertext . $mac);
        if ($message->footer !== '') {
            $token .= '.' . self::encode($message->footer);
        }

        return new Paseto($token);
    }

    public static function decrypt(
        SharedKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage {
        if ($token->version !== self::VERSION || $token->purpose !== PasetoPurpose::Local) {
            throw new PasetoCryptoException('Paseto Version/Purpose Mismatch');
        }

        [,, $payload, $footer] = \explode('.', $token->value, 4) + ['', '', '', ''];

        $payload = self::decode($payload);
        $nonce = \substr($payload, 0, self::NONCE_BYTES);
        $ciphertext = \substr($payload, self::NONCE_BYTES, -self::NONCE_BYTES);
        $mac = \substr($payload, -self::NONCE_BYTES);
        $footer = $footer ? self::decode($footer) : '';

        $authentication_key = \sodium_crypto_generichash(self::HKDF_INFO_AUTH . $nonce, $key->bytes(), self::KEY_BYTES);
        $pae = Util::pae(self::HEADER_LOCAL, $nonce, $ciphertext, $footer, $additional_data);
        $authentication_tag = \sodium_crypto_generichash($pae, $authentication_key, self::KEY_BYTES);
        \sodium_memzero($authentication_key);

        if (! \hash_equals($authentication_tag, $mac)) {
            throw new PasetoCryptoException('Invalid Token Signature');
        }

        $prk = \sodium_crypto_generichash(self::HKDF_INFO_SBOX . $nonce, $key->bytes(), self::KEY_BYTES + self::STREAM_NONCE_BYTES);
        $encryption_key = \substr($prk, 0, self::KEY_BYTES);
        $encryption_nonce = \substr($prk, self::KEY_BYTES, self::STREAM_NONCE_BYTES);
        $plaintext = \sodium_crypto_stream_xchacha20_xor($ciphertext, $encryption_nonce, $encryption_key);
        \sodium_memzero($prk);
        \sodium_memzero($encryption_key);
        \sodium_memzero($encryption_nonce);

        return new PasetoMessage($plaintext, $footer);
    }

    /**
     * Version 4 signatures are almost the same as Version 2, except the later
     * protocol does not support including implicit claims in the signature.
     *
     * Algorithm Lucidity: The SignatureKeyPair is a self-valid Ed25519 key pair
     * and is valid for use with v4.public tokens.
     *
     * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version4.md#sign
     */
    public static function sign(
        SignatureKeyPair $key_pair,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto {
        $encoded = Util::pae(self::HEADER_PUBLIC, $message->payload, $message->footer, $additional_data);
        $signature = \sodium_crypto_sign_detached($encoded, $key_pair->secret->bytes());
        $token = self::HEADER_PUBLIC . self::encode($message->payload . $signature);
        if ($message->footer !== '') {
            $token .= '.' . self::encode($message->footer);
        }

        return new Paseto($token);
    }

    public static function verify(
        SignaturePublicKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage {
        if ($token->version !== self::VERSION || $token->purpose !== PasetoPurpose::Public) {
            throw new PasetoCryptoException('Paseto Version/Purpose Mismatch');
        }

        [,, $payload, $footer] = \explode('.', $token->value, 4) + ['', '', '', ''];

        $payload = self::decode($payload);
        $message = \substr($payload, 0, -\SODIUM_CRYPTO_SIGN_BYTES);
        $signature = \substr($payload, -\SODIUM_CRYPTO_SIGN_BYTES) ?: throw new PasetoCryptoException('Missing Signature');
        $footer = $footer ? self::decode($footer) : '';

        $encoded = Util::pae(self::HEADER_PUBLIC, $message, $footer, $additional_data);
        if (\sodium_crypto_sign_verify_detached($signature, $encoded, $key->bytes())) {
            return new PasetoMessage($message, $footer);
        }

        throw new PasetoCryptoException('Invalid Token Signature');
    }

    /**
     * The PASETO Standard requires stricter parsing and decoding of Base64Url
     * encoded strings than standard encoding code.
     */
    private static function decode(string $encoded): string
    {
        try {
            return ConstantTimeEncoder::decode(Encoding::Base64UrlNoPadding, $encoded, true);
        } catch (\Throwable $ex) {
            throw new PasetoCryptoException('Invalid Encoding', previous: $ex);
        }
    }

    private static function encode(string $value): string
    {
        return ConstantTimeEncoder::encode(Encoding::Base64UrlNoPadding, $value);
    }
}
