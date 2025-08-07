<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Result;

use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\Match\RouteMatch;
use PhoneBurner\Pinch\Component\Http\Routing\RouterResult;

final readonly class RouteFound implements RouterResult
{
    private RouteMatch $match;

    /**
     * @param array<string, string> $path_parameters
     */
    public function __construct(RouteDefinition $definition, array $path_parameters)
    {
        $this->match = RouteMatch::make($definition, $path_parameters);
    }

    /**
     * @param array<string, string> $path_parameters
     */
    public static function make(RouteDefinition $definition, array $path_parameters = []): self
    {
        return new self($definition, $path_parameters);
    }

    #[\Override]
    public function isFound(): bool
    {
        return true;
    }

    #[\Override]
    public function getRouteMatch(): RouteMatch
    {
        return $this->match;
    }
}
