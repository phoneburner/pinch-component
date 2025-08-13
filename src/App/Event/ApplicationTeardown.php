<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\Event;

use PhoneBurner\Pinch\Attribute\Psr14Event;
use PhoneBurner\Pinch\Component\App\App;

#[Psr14Event]
final readonly class ApplicationTeardown
{
    public function __construct(public App $app)
    {
    }
}
