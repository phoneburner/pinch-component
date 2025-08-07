<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoMessage;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;

interface PasetoProtocol
{
    public static function encrypt(
        SharedKey $key,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto;

    public static function decrypt(
        SharedKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage;

    public static function sign(
        SignatureKeyPair $key_pair,
        PasetoMessage $message,
        string $additional_data = '',
    ): Paseto;

    public static function verify(
        SignaturePublicKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage;
}
