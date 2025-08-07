<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims;

use PhoneBurner\Pinch\Time\Standards\Rfc3339;
use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;
use PhoneBurner\Pinch\Time\TimeZone\Tz;
use PhoneBurner\Pinch\Uuid\OrderedUuid;

use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

readonly class PasetoPayloadClaims implements \JsonSerializable
{
    public \DateTimeImmutable $iat;

    public \DateTimeImmutable $nbf;

    public \DateTimeImmutable $exp;

    public OrderedUuid $jti;

    /**
     * @param array<string, mixed> $other Custom claims to add to the payload, must not contain reserved claim names
     */
    public function __construct(
        public \Stringable|string|null $iss = null,
        public \Stringable|string|null $sub = null,
        public \Stringable|string|null $aud = null,
        \DateTimeImmutable $iat = new \DateTimeImmutable(),
        \DateTimeImmutable|null $nbf = null,
        \DateTimeImmutable|TimeInterval $exp = new TimeInterval(seconds: 10 * SECONDS_IN_MINUTE),
        public array $other = [],
    ) {
        $this->jti = new OrderedUuid();

        // The time should already be in UTC, but to comply with the spec, we'll make sure it is.
        $this->iat = $iat->getOffset() !== 0 ? $iat->setTimezone(Tz::Utc->timezone()) : $iat;

        $this->nbf = match (true) {
            $nbf === null => $iat,
            $nbf->getOffset() !== 0 => $nbf->setTimezone(Tz::Utc->timezone()),
            default => $nbf,
        };

        $this->exp = match (true) {
            $exp instanceof \DateInterval => $iat->add($exp),
            $exp->getOffset() !== 0 => $exp->setTimezone(Tz::Utc->timezone()),
            default => $exp,
        };

        if ($this->iat >= $this->exp) {
            throw new \InvalidArgumentException('Expiration must be after the issued at time');
        }

        if ($this->nbf >= $this->exp) {
            throw new \InvalidArgumentException('Expiration must be after the not before time');
        }

        if (\array_intersect(\array_keys($this->other), RegisteredPayloadClaim::values())) {
            throw new \InvalidArgumentException('Custom claims cannot contain reserved claim names');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $registered_claims = [
            RegisteredPayloadClaim::Issuer->value => (string)$this->iss,
            RegisteredPayloadClaim::Subject->value => (string)$this->sub,
            RegisteredPayloadClaim::Audience->value => (string)$this->aud,
            RegisteredPayloadClaim::IssuedAt->value => $this->iat->format(Rfc3339::DATETIME_Z),
            RegisteredPayloadClaim::NotBefore->value => $this->nbf->format(Rfc3339::DATETIME_Z),
            RegisteredPayloadClaim::Expiration->value => $this->exp->format(Rfc3339::DATETIME_Z),
            RegisteredPayloadClaim::TokenId->value => $this->jti->toString(),
        ];

        return [...\array_filter($registered_claims), ...$this->other];
    }
}
