<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

class InvalidSignature extends CryptographicRuntimeException
{
    public static function length(int $expected): self
    {
        return new self(\sprintf("Message Signature Must Be Exactly %d Bytes", $expected));
    }
}
