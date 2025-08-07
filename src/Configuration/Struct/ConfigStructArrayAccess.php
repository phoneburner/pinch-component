<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\Struct;

/**
 * @implements \ArrayAccess<string, mixed>
 * @phpstan-require-implements \ArrayAccess
 */
trait ConfigStructArrayAccess
{
    public function offsetExists(mixed $offset): bool
    {
        return \property_exists($this, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return \property_exists($this, $offset) ? $this->{$offset} : null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Config Structs are Immutable');
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Config Structs are Immutable');
    }
}
