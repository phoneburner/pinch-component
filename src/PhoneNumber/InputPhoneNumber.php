<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumberAware;

/**
 * A PhoneNumber instance (i.e., a number representable as a valid E.164 format
 * string) that has been parsed from a raw input string, retaining that original
 * string for potential later use.
 */
#[Contract]
final readonly class InputPhoneNumber implements
    PhoneNumber,
    PhoneNumberAware,
    \Stringable
{
    public E164 $value;

    public function __construct(public string $input)
    {
        $this->value = E164::make($input);
    }

    public static function make(string $input): self
    {
        return new self($input);
    }

    #[\Override]
    public function toE164(): E164
    {
        return $this->value;
    }

    #[\Override]
    public function getValue(): PhoneNumber
    {
        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
