<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\Exception;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
interface BootError extends \Throwable
{
}
