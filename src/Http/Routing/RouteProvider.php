<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing;

use PhoneBurner\Pinch\Component\Http\Routing\Definition\Definition;

interface RouteProvider
{
    /**
     * @return array<Definition>
     */
    public function __invoke(): array;
}
