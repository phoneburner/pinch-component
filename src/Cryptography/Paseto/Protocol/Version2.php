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
 * Version 2: Sodium Original
 * v2.local XChaCha20-Poly1305-IETF
 * v2.public Ed25519
 *
 * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version2.md
 */
final class Version2 implements PasetoProtocol
{
    public const PasetoVersion VERSION = PasetoVersion::V2;
    public const string HEADER_PUBLIC = PasetoVersion::V2->value . '.public.';
    public const string HEADER_LOCAL = PasetoVersion::V2->value . '.local.';

    private const int NONCE_BYTES = \SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

    public static function encrypt(SharedKey $key, PasetoMessage $message, string $additional_data = ''): Paseto
    {
        // Compute a 24-byte BLAKE2b hash of the message, using 24 random bytes as the key
        $nonce = \sodium_crypto_generichash($message->payload, \random_bytes(self::NONCE_BYTES), self::NONCE_BYTES);
        $additional_data = Util::pae(self::HEADER_LOCAL, $nonce, $message->footer);
        $ciphertext = \sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $message->payload,
            $additional_data,
            $nonce,
            $key->bytes(),
        );

        $token = self::HEADER_LOCAL . self::encode($nonce . $ciphertext);
        if ($message->footer !== '') {
            $token .= '.' . self::encode($message->footer);
        }

        return new Paseto($token);
    }

    public static function decrypt(SharedKey $key, Paseto $token, string $additional_data = ''): PasetoMessage
    {
        if ($token->version !== self::VERSION || $token->purpose !== PasetoPurpose::Local) {
            throw new PasetoCryptoException('Paseto Version/Purpose Mismatch');
        }

        [,, $payload, $footer] = \explode('.', $token->value, 4) + ['', '', '', ''];

        $payload = self::decode($payload);
        $nonce = \substr($payload, 0, self::NONCE_BYTES);
        $ciphertext = \substr($payload, self::NONCE_BYTES);
        $footer = $footer ? self::decode($footer) : '';

        $additional_data = Util::pae(self::HEADER_LOCAL, $nonce, $footer);
        $data = \sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            $additional_data,
            $nonce,
            $key->bytes(),
        );

        if ($data) {
            return new PasetoMessage($data, $footer);
        }

        return throw new PasetoCryptoException('Invalid Token');
    }

    public static function sign(
        SignatureKeyPair $key_pair,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto {
        $encoded = Util::pae(self::HEADER_PUBLIC, $message->payload, $message->footer);
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

        $encoded = Util::pae(self::HEADER_PUBLIC, $message, $footer);
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
