<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Definition;

use Generator;
use PhoneBurner\Pinch\Component\Http\Routing\Route;
use UnexpectedValueException;

/**
 * @implements \IteratorAggregate<RouteDefinition>
 */
class InMemoryDefinitionList implements DefinitionList, \IteratorAggregate
{
    /**
     * @var array<Definition>
     */
    private array $definitions;

    /**
     * @var array<RouteDefinition>
     */
    private array $named = [];

    public function __construct(Definition ...$definitions)
    {
        $this->definitions = $definitions;

        foreach ($this as $definition) {
            $name = $definition->getAttributes()[Route::class] ?? null;
            if ($name) {
                $this->named[$name] = $definition;
            }
        }
    }

    public static function make(Definition ...$definitions): self
    {
        return new self(...$definitions);
    }

    #[\Override]
    public function getNamedRoute(string $name): RouteDefinition
    {
        if (! $this->hasNamedRoute($name)) {
            throw new \LogicException('invalid name: ' . $name);
        }

        return $this->named[$name];
    }

    #[\Override]
    public function hasNamedRoute(string $name): bool
    {
        return isset($this->named[$name]);
    }

    /**
     * @return Generator<RouteDefinition>
     */
    #[\Override]
    public function getIterator(): Generator
    {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof RouteGroupDefinition) {
                yield from $definition;
                continue;
            }

            if (! $definition instanceof RouteDefinition) {
                throw new UnexpectedValueException(\sprintf(
                    "%s Not Instance Of %s",
                    \get_debug_type($definition),
                    RouteDefinition::class,
                ));
            }

            yield $definition;
        }
    }

    /**
     * @return array{definitions: array<Definition>}
     */
    public function __serialize(): array
    {
        return [
            'definitions' => $this->definitions,
        ];
    }

    /**
     * @param array{definitions: array<Definition>} $data
     */
    public function __unserialize(array $data): void
    {
        $this->definitions = $data['definitions'];
    }
}
