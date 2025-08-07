<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Lock;

interface NamedKeyFactory
{
    public function make(\Stringable|string $name): NamedKey;

    public function has(NamedKey|\Stringable|string $name): bool;

    public function delete(NamedKey|\Stringable|string $name): void;

    public static function serialize(NamedKey $key): string;

    public static function deserialize(string $key): NamedKey;
}
