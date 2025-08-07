<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Definition;

use IteratorAggregate;
use PhoneBurner\Pinch\Component\Http\Routing\RouteProvider;

use function PhoneBurner\Pinch\Array\array_wrap;

/**
 * @implements IteratorAggregate<RouteDefinition>
 */
class LazyConfigDefinitionList implements DefinitionList, IteratorAggregate
{
    /**
     * @var array<RouteProvider|callable(): (Definition|iterable<Definition>)>
     */
    private readonly array $callables;

    private InMemoryDefinitionList|null $definition_list = null;

    /**
     * @param RouteProvider|callable(): (Definition|iterable<Definition>) ...$callables
     */
    public function __construct(RouteProvider|callable ...$callables)
    {
        $this->callables = $callables;
    }

    /**
     * @param array<RouteProvider|callable(): (Definition|iterable<Definition>)> $route_factories
     */
    public static function makeFromArray(array $route_factories): self
    {
        return new self(...\array_values($route_factories));
    }

    /**
     * @param RouteProvider|callable(): Definition ...$callables
     */
    public static function makeFromCallable(RouteProvider|callable ...$callables): self
    {
        return new self(...$callables);
    }

    private function getWrapped(): DefinitionList
    {
        return $this->definition_list ??= InMemoryDefinitionList::make(...$this->load());
    }

    /**
     * @return \Generator<Definition>
     */
    private function load(): \Generator
    {
        foreach ($this->callables as $loader) {
            \assert(\is_callable($loader));
            foreach (array_wrap($loader()) as $definition) {
                \assert($definition instanceof Definition);
                yield $definition;
            }
        }
    }

    #[\Override]
    public function getNamedRoute(string $name): RouteDefinition
    {
        return $this->getWrapped()->getNamedRoute($name);
    }

    #[\Override]
    public function hasNamedRoute(string $name): bool
    {
        return $this->getWrapped()->hasNamedRoute($name);
    }

    #[\Override]
    public function getIterator(): \Generator
    {
        yield from $this->getWrapped();
    }
}
