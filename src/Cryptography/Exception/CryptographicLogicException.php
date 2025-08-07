<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicException;

class CryptographicLogicException extends \LogicException implements CryptographicException
{
    public static function unreachable(): self
    {
        return new self('A code path was executed that would not normally be possible under normal operation.');
    }
}
