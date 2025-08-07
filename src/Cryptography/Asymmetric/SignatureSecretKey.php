<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyId;
use PhoneBurner\Pinch\Component\Cryptography\String\FixedLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringProhibitsSerialization;

/**
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class SignatureSecretKey extends FixedLengthSensitiveBinaryString implements SecretKey
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringImportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_SIGN_SECRETKEYBYTES; // 512-bit string

    public function id(): KeyId
    {
        return SignatureKeyPair::fromSecretKey($this)->id();
    }

    public function secret(): static
    {
        return $this;
    }
}
