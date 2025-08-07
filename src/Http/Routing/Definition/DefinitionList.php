<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Definition;

use Traversable;

/**
 * @extends Traversable<RouteDefinition>
 */
interface DefinitionList extends Traversable
{
    public function hasNamedRoute(string $name): bool;

    public function getNamedRoute(string $name): RouteDefinition;
}
