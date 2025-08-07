<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\String;

final class Nonce extends VariableLengthSensitiveBinaryString
{
    public static function generate(int $length = 32): self
    {
        \assert($length > 0 && $length <= \PHP_INT_MAX);
        return new self(\random_bytes($length));
    }
}
