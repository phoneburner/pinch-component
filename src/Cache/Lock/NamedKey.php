<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Lock;

interface NamedKey extends \Stringable
{
    // phpcs:ignore
    public string $name { get; }

    public function __serialize(): array;

    /**
     * @param array<array-key, mixed> $data
     */
    public function __unserialize(array $data): void;
}
