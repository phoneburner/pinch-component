<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\String\FixedLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;

final class SignatureKeyPairSeed extends FixedLengthSensitiveBinaryString
{
    use BinaryStringExportBehavior;
    use BinaryStringImportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_SIGN_SEEDBYTES; // 32 bytes

    public static function generate(): static
    {
        return new self(\random_bytes(self::LENGTH));
    }
}
