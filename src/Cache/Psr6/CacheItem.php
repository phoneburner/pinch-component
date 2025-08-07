<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PhoneBurner\Pinch\Time\Clock\SystemClock;
use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    public function __construct(
        public readonly CacheKey $key,
        private readonly Clock $clock = new SystemClock(),
        private mixed $value = null,
        private \DateTimeInterface|null $expiration = null,
        private bool $hit = false,
    ) {
    }

    public function getKey(): string
    {
        return $this->key->normalized;
    }

    public function get(): mixed
    {
        return $this->isHit() ? $this->value : null;
    }

    public function isHit(): bool
    {
        return match (true) {
            $this->hit === false => false,
            $this->expiration === null => true,
            default => $this->clock->now() < $this->expiration,
        };
    }

    public function set(mixed $value): static
    {
        $this->hit = true;
        $this->value = $value;
        return $this;
    }

    public function expiresAt(\DateTimeInterface|null $expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        return match (true) {
            $time === null => $this->expiresAt(null),
            $time instanceof \DateInterval => $this->expiresAt($this->clock->now()->add($time)),
            default => $this->expiresAt($this->clock->now()->addSeconds($time))
        };
    }
}
