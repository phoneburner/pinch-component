<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims;

use PhoneBurner\Pinch\String\Encoding\Json;

use function PhoneBurner\Pinch\Type\cast_nullable_datetime;
use function PhoneBurner\Pinch\Type\cast_nullable_string;

/**
 * @implements \ArrayAccess<string, mixed>
 */
readonly class DecodedPayloadClaims implements \ArrayAccess
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public string|null $iss = null,
        public string|null $sub = null,
        public string|null $aud = null,
        public string|null $jti = null,
        public \DateTimeImmutable|null $iat = null,
        public \DateTimeImmutable|null $exp = null,
        public \DateTimeImmutable|null $nbf = null,
        public array $claims = [],
    ) {
    }

    public static function make(string $json_encoded_claims): self
    {
        if ($json_encoded_claims === '') {
            return new self();
        }

        $claims = Json::decode($json_encoded_claims);

        return new self(
            cast_nullable_string($claims[RegisteredPayloadClaim::Issuer->value] ?? null),
            cast_nullable_string($claims[RegisteredPayloadClaim::Subject->value] ?? null),
            cast_nullable_string($claims[RegisteredPayloadClaim::Audience->value] ?? null),
            cast_nullable_string($claims[RegisteredPayloadClaim::TokenId->value] ?? null),
            cast_nullable_datetime($claims[RegisteredPayloadClaim::IssuedAt->value] ?? null),
            cast_nullable_datetime($claims[RegisteredPayloadClaim::Expiration->value] ?? null),
            cast_nullable_datetime($claims[RegisteredPayloadClaim::NotBefore->value] ?? null),
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
