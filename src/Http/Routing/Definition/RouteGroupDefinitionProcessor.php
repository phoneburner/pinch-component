<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Definition;

interface RouteGroupDefinitionProcessor
{
    public function __invoke(RouteGroupDefinition $definition): RouteGroupDefinition;
}
