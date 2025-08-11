<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

use function PhoneBurner\Pinch\String\str_snake;
use function PhoneBurner\Pinch\Type\cast_nullable_string;

/**
 * Creates a PSR-6/PSR-16 safe cache key "namespaced" by the passed in parts
 *
 * e.g. (string)CacheKey::make('user', 1, 'FooBarProfile') would return 'user.1.foo_bar_profile'
 */
#[Contract]
readonly class CacheKey implements \Stringable
{
    private const array RESERVED_CHARACTERS = [':', '{', '}', '(', ')', '/', '\\', '@'];

    public string $normalized;

    public function __construct(\Stringable|\BackedEnum|string|int ...$key_parts)
    {
        $this->normalized = \implode('.', \array_map(static function (\Stringable|\BackedEnum|string|int $part): string {
            $part = \implode('.', \array_map(static function (string $subpart): string {
                return str_snake(\str_replace(self::RESERVED_CHARACTERS, '_', $subpart));
            }, \explode('.', \trim(cast_nullable_string($part), '.'))));
            return $part !== '' ? $part : throw new \InvalidArgumentException('Cache key part cannot be empty string');
        }, $key_parts));
    }

    public static function make(\Stringable|\BackedEnum|string|int ...$key_parts): self
    {
        return new self(...$key_parts);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->normalized;
    }
}
