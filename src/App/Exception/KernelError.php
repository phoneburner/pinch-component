<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\Exception;

use LogicException;

class KernelError extends LogicException implements BootError
{
}
