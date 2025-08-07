<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

class InvalidKeyPair extends CryptographicRuntimeException
{
    public static function length(int $expected): self
    {
        return new self(\sprintf("Key Pair Must Be Exactly %d Bytes", $expected));
    }
}
