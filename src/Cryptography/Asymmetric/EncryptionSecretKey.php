<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\String\FixedLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringProhibitsSerialization;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class EncryptionSecretKey extends FixedLengthSensitiveBinaryString implements SecretKey
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringImportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_KX_SECRETKEYBYTES; // 256-bit string

    public function secret(): static
    {
        return $this;
    }
}
