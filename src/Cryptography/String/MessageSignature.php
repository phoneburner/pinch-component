<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\String;

use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;

/**
 * A 512-bit digital signature created with either the Symmetric::sign() or
 * Asymmetric::sign() methods.
 */
final class MessageSignature extends FixedLengthSensitiveBinaryString
{
    use BinaryStringImportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX; // 512-bit digest

    public function first(int $n): VariableLengthSensitiveBinaryString
    {
        return new VariableLengthSensitiveBinaryString(\substr($this->bytes(), 0, $n));
    }
}
