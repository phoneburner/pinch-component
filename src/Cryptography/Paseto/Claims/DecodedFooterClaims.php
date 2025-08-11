<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims;

use PhoneBurner\Pinch\String\Encoding\Json;

use function PhoneBurner\Pinch\Type\cast_nullable_string;

/**
 * @implements \ArrayAccess<string, mixed>
 */
readonly class DecodedFooterClaims implements \ArrayAccess
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public string|null $kid = null,
        public string|null $wpk = null,
        public array $claims = [],
    ) {
    }

    public static function make(string $json_encoded_claims): self
    {
        if ($json_encoded_claims === '') {
            return new self();
        }

        // The PASETO Spec requires that we validate that the footer is a valid JSON object
        // before decoding it, in order to prevent potential DDOS and similar attacks.
        \json_validate($json_encoded_claims) || throw new \InvalidArgumentException('Invalid JSON in footer');

        $claims = Json::decode($json_encoded_claims);

        return new self(
            cast_nullable_string($claims[RegisteredFooterClaim::KeyId->value] ?? null),
            cast_nullable_string($claims[RegisteredFooterClaim::WrappedPaserk->value] ?? null),
            $claims,
        );
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->claims);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->claims[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Decoded Claims are Immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('Decoded Claims are Immutable');
    }
}
