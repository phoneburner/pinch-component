<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing;

use Psr\Http\Message\UriInterface;

/**
 * General route interface allowing a URI to be generated from a route given
 * parameters. Concrete implementations provide additional contextual behaviour.
 */
interface Route extends UriInterface
{
    public function withPathParameter(string $name, string $value): self;

    /**
     * @param array<string, string> $params
     */
    public function withPathParameters(array $params): self;
}
