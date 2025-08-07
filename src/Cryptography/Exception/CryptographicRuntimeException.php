<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicException;

class CryptographicRuntimeException extends \RuntimeException implements CryptographicException
{
}
