<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Symmetric;

use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\Key;
use PhoneBurner\Pinch\Component\Cryptography\String\FixedLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringFromRandomBytes;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringProhibitsSerialization;

/**
 * 256-bit symmetric key for use with XChaCha20 or AEGIS-256 ciphers
 *
 * Note: this class is intentionally not readonly, as this allows us to explicitly
 * zero out the key in memory when the object is destroyed.
 */
final class SharedKey extends FixedLengthSensitiveBinaryString implements Key
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringFromRandomBytes;
    use BinaryStringImportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_STREAM_XCHACHA20_KEYBYTES;
}
