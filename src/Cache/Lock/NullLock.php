<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Lock;

use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;

final readonly class NullLock implements Lock
{
    public function __construct(
        private TimeInterval|null $ttl = null,
        private bool $acquire = true,
        private bool $acquired = true,
    ) {
    }

    #[\Override]
    public function acquire(
        bool $blocking = false,
        int $timeout_seconds = 30,
        int $delay_microseconds = 25000,
        SharedLockMode $mode = SharedLockMode::Write,
    ): bool {
        return $this->acquire;
    }

    #[\Override]
    public function release(): void
    {
    }

    #[\Override]
    public function refresh(TimeInterval|null $ttl = null): void
    {
    }

    #[\Override]
    public function acquired(): bool
    {
        return $this->acquired;
    }

    #[\Override]
    public function ttl(): TimeInterval|null
    {
        return $this->ttl;
    }
}
