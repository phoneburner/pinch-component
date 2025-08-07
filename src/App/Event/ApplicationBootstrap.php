<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\Event;

use PhoneBurner\Pinch\Component\App\App;

final readonly class ApplicationBootstrap
{
    public function __construct(public App $app)
    {
    }
}
