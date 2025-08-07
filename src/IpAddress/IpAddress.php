<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\IpAddress;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\IpAddress\Exception\InvalidIpAddress;

#[Contract]
readonly class IpAddress implements \Stringable
{
    public IpAddressType $type;

    public function __construct(public string $value)
    {
        \filter_var($value, \FILTER_VALIDATE_IP) ?: throw new InvalidIpAddress('invalid ip address: ' . $value);
        $this->type = \str_contains($this->value, ':') ? IpAddressType::IPv6 : IpAddressType::IPv4;
    }

    public static function make(string $address): self
    {
        return new self($address);
    }

    public static function tryFrom(mixed $address): self|null
    {
        try {
            return match (true) {
                $address instanceof self => $address,
                \is_string($address) => $address !== '' ? new self($address) : null,
                $address instanceof \Stringable => self::tryFrom((string)$address),
                default => null,
            };
        } catch (InvalidIpAddress) {
            return null;
        }
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    public function __serialize(): array
    {
        return ['value' => $this->value];
    }

    /**
     * @param array{value: string} $data
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data['value']);
    }

    /**
     * @param array<string, mixed> $data Often $_SERVER, but could be any array with IP address headers
     */
    public static function marshall(array $data): self|null
    {
        $addresses = $data['HTTP_TRUE_CLIENT_IP']
            ?? $data['HTTP_X_FORWARDED_FOR']
            ?? $data['REMOTE_ADDR']
            ?? null;

        if ($addresses === null) {
            return null;
        }

        \assert(\is_scalar($addresses));

        // use left-most address since the ones to the right are the prox(y|ies).
        $addresses = \explode(',', (string)$addresses);

        return self::tryFrom(\trim(\reset($addresses)));
    }

    public static function local(): self
    {
        return new self(\gethostbyname(\gethostname() ?: 'localhost'));
    }
}
