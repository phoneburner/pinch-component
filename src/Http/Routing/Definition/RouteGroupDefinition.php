<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Definition;

use Generator;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Routing\Route;
use PhoneBurner\Pinch\String\Serialization\PhpSerializable;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function PhoneBurner\Pinch\Type\narrow_array;

/**
 * @implements \IteratorAggregate<RouteDefinition>
 * @implements PhpSerializable<array{
 *     path: string,
 *     methods: list<value-of<HttpMethod>>,
 *     attributes: array<string,mixed>,
 *     routes: array<RouteDefinition>,
 *     groups: array<RouteGroupDefinition>,
 * }>
 */
class RouteGroupDefinition implements Definition, \IteratorAggregate, PhpSerializable
{
    use DefinitionBehaviour;

    /**
     * @param array<HttpMethod|value-of<HttpMethod>> $methods
     * @param array<string,mixed> $attributes
     * @param array<RouteDefinition> $routes
     * @param array<RouteGroupDefinition> $groups
     */
    private function __construct(
        string $path,
        array $methods,
        array $attributes,
        private array $routes,
        private array $groups,
    ) {
        $this->path = $path;
        $this->setMethods(...$methods);
        $this->setAttributes($attributes);
    }

    /**
     * @return \Generator<RouteDefinition>
     */
    private function getRoutes(): Generator
    {
        yield from $this->routes;
        foreach ($this->groups as $group) {
            yield from $group;
        }
    }

    /**
     * @param array<HttpMethod|value-of<HttpMethod>> $methods
     * @param array<string,mixed> $attributes
     */
    public static function make(string $path, array $methods = [], array $attributes = []): self
    {
        return new self($path, $methods, $attributes, [], []);
    }

    /**
     * @return \Iterator<RouteDefinition>
     */
    #[\Override]
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator(\array_map(function (RouteDefinition $route): RouteDefinition {
            $attributes = $this->attributes;
            $route_attributes = $route->getAttributes();

            $name = \implode('.', \array_filter([
                $attributes[Route::class] ?? '',
                $route_attributes[Route::class] ?? '',
            ]));

            if ($name) {
                $attributes[Route::class] = $name;
            }

            $middleware = \array_merge(
                narrow_array($attributes[MiddlewareInterface::class] ?? []),
                narrow_array($route_attributes[MiddlewareInterface::class] ?? []),
            );

            if ($middleware) {
                $attributes[MiddlewareInterface::class] = $middleware;
            }

            return $route->withRoutePath($this->path . $route->getRoutePath())
                ->withAddedMethod(...\array_map(HttpMethod::instance(...), $this->methods))
                ->withAddedAttributes($attributes);
        }, \iterator_to_array($this->getRoutes(), false)));
    }

    #[\Override]
    public function __serialize(): array
    {
        return [
            'path' => $this->path,
            'methods' => $this->methods,
            'attributes' => $this->attributes,
            'routes' => $this->routes,
            'groups' => $this->groups,
        ];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->path = $data['path'];
        $this->methods = $data['methods'];
        $this->attributes = $data['attributes'];
        $this->routes = $data['routes'] ?? [];
        $this->groups = $data['groups'] ?? [];
    }

    public function withRoutes(RouteDefinition ...$routes): self
    {
        return new self($this->path, $this->methods, $this->attributes, $routes, $this->groups);
    }

    public function withAddedRoutes(RouteDefinition ...$routes): self
    {
        return new self($this->path, $this->methods, $this->attributes, [
            ...$this->routes,
            ...$routes,
        ], $this->groups);
    }

    public function withGroups(self ...$groups): self
    {
        return new self($this->path, $this->methods, $this->attributes, $this->routes, $groups);
    }

    public function withAddedGroups(self ...$groups): self
    {
        return new self($this->path, $this->methods, $this->attributes, $this->routes, [
            ...$this->groups,
            ...$groups,
        ]);
    }

    #[\Override]
    public function withRoutePath(string $path): self
    {
        return new self($path, $this->methods, $this->attributes, $this->routes, $this->groups);
    }

    #[\Override]
    public function withMethod(HttpMethod ...$methods): self
    {
        return new self($this->path, $methods, $this->attributes, $this->routes, $this->groups);
    }

    #[\Override]
    public function withAddedMethod(HttpMethod ...$methods): self
    {
        return new self($this->path, [
            ...$this->methods,
            ...$methods,
        ], $this->attributes, $this->routes, $this->groups);
    }

    #[\Override]
    public function withName(string $name): self
    {
        return $this->withAttribute(Route::class, $name);
    }

    /**
     * @param class-string<RequestHandlerInterface> $handler_class
     */
    #[\Override]
    public function withHandler(string $handler_class): self
    {
        return $this->withAttribute(RequestHandlerInterface::class, $handler_class);
    }

    /**
     * @param class-string<MiddlewareInterface> ...$middleware
     */
    #[\Override]
    public function withMiddleware(string ...$middleware): self
    {
        return $this->withAttribute(MiddlewareInterface::class, $middleware);
    }

    /**
     * Appends the middleware classes to any existing middleware in the definition
     *
     * @param class-string<MiddlewareInterface> ...$middleware
     */
    #[\Override]
    public function withAddedMiddleware(string ...$middleware): self
    {
        return $this->withAttribute(
            MiddlewareInterface::class,
            [...($this->attributes[MiddlewareInterface::class] ?? []), ...$middleware],
        );
    }

    #[\Override]
    public function withAttribute(string $name, mixed $value): self
    {
        return new self($this->path, $this->methods, \array_merge(
            $this->attributes,
            [$name => $value],
        ), $this->routes, $this->groups);
    }

    #[\Override]
    public function withAttributes(array $attributes): self
    {
        return new self($this->path, $this->methods, $attributes, $this->routes, $this->groups);
    }

    #[\Override]
    public function withAddedAttributes(array $attributes): self
    {
        return new self($this->path, $this->methods, [...$this->attributes, ...$attributes], $this->routes, $this->groups);
    }
}
