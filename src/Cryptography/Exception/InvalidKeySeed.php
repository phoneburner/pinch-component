<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

class InvalidKeySeed extends CryptographicRuntimeException
{
    public static function length(int $expected): self
    {
        return new self(\sprintf("Key Seed Must Be Exactly %d Bytes", $expected));
    }
}
