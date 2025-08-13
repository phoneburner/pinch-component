<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\App\Kernel;

#[Psr14Event]
final readonly class KernelExecutionStarted
{
    public function __construct(public Kernel $kernel)
    {
    }
}
