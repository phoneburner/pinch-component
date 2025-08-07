<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicRuntimeException;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoException;

class PasetoCryptoException extends CryptographicRuntimeException implements PasetoException
{
}
