<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

class InvalidStringLength extends CryptographicRuntimeException
{
    public function __construct(int $expected)
    {
        parent::__construct(\sprintf('String Must Be Exactly %d Bytes', $expected));
    }
}
