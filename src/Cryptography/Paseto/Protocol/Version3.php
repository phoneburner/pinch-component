<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoCryptoException;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoMessage;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoVersion;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;

/**
 * Version 3: NIST Modern
 */
class Version3 implements PasetoProtocol
{
    public const PasetoVersion VERSION = PasetoVersion::V3;
    public const string HEADER_PUBLIC = PasetoVersion::V3->value . '.public.';
    public const string HEADER_LOCAL = PasetoVersion::V3->value . '.local.';

    public static function encrypt(
        SharedKey $key,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }

    public static function decrypt(
        SharedKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }

    public static function sign(
        SignatureKeyPair $key_pair,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }

    public static function verify(
        SignaturePublicKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage {
        throw new PasetoCryptoException('Unsupported Paseto Protocol Version');
    }
}
