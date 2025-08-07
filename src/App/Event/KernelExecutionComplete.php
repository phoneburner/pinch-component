<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\Event;

use PhoneBurner\Pinch\Component\App\Kernel;

final readonly class KernelExecutionComplete
{
    public function __construct(public Kernel $kernel)
    {
    }
}
