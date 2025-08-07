<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use Psr\Container\ContainerInterface;

#[Contract]
interface Configuration extends ContainerInterface
{
    /**
     * Returns true if the configuration has a non-null value set for the
     * given key, supporting dot-notation lookups.
     */
    public function has(string $id): bool;

    /**
     * Gets a configuration value by key (dot notation),
     * returning null if no value is set.
     */
    public function get(string $id): mixed;
}
