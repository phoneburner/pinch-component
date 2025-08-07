<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims;

class PasetoFooterClaims implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $other Custom claims to add to the payload, must not contain reserved claim names
     */
    public function __construct(
        public \Stringable|string|null $kid = null,
        public \Stringable|string|null $wpk = null,
        public array $other = [],
    ) {
        if (\array_intersect(\array_keys($this->other), RegisteredFooterClaim::values())) {
            throw new \InvalidArgumentException('Custom claims cannot contain reserved claim names');
        }
    }

    public function isEmpty(): bool
    {
        return ! ($this->kid || $this->wpk || $this->other);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        // shortcut the most common case:
        if ($this->kid === null && $this->wpk === null) {
            return $this->other;
        }

        $registered_claims = [
            RegisteredFooterClaim::KeyId->value => (string)$this->kid,
            RegisteredFooterClaim::WrappedPaserk->value => (string)$this->wpk,
        ];

        return [...\array_filter($registered_claims), ...$this->other];
    }
}
