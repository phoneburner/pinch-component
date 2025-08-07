<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
interface AppendOnlyCache
{
    /**
     * Retrieve an item from the cache by key. Use this method to also check if
     * an item exists in the cache, e.g. in place of `has()`.
     */
    public function get(string|\Stringable $key): mixed;

    /**
     * Get multiple items from the cache in a single operation
     *
     * @param iterable<string|\Stringable> $keys
     * @return iterable<string, mixed> Will return an array of key => value pairs,
     * returning null for keys that do not exist. The array will be indexed by the
     * normalized form of the keys passed in (necessary to support stringable objects).
     */
    public function getMultiple(iterable $keys): iterable;

    /**
     * Store an item in the cache for a given number of seconds.
     */
    public function set(string|\Stringable $key, mixed $value): bool;

    /**
     * Set multiple items in the cache in a single operation
     *
     * @param iterable<mixed> $values (key => value)
     */
    public function setMultiple(iterable $values): bool;

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function remember(string|\Stringable $key, callable $callback): mixed;
}
