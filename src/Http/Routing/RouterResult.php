<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing;

use PhoneBurner\Pinch\Component\Http\Routing\Match\RouteMatch;

interface RouterResult
{
    public function isFound(): bool;

    public function getRouteMatch(): RouteMatch;
}
