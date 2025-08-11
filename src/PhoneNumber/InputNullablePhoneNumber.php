<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumberAware;

/**
 * A NullablePhoneNumber instance (i.e., a number representable as a valid E.164 format
 * string) that has been parsed from a raw input string, retaining that original
 * string for potential later use.
 */
#[Contract]
final readonly class InputNullablePhoneNumber implements
    NullablePhoneNumber,
    NullablePhoneNumberAware,
    \Stringable
{
    public E164|null $value;

    public function __construct(public string $intput)
    {
        $this->value = E164::tryFrom($intput);
    }

    public static function make(string $input): self
    {
        return new self($input);
    }

    #[\Override]
    public function toE164(): E164|null
    {
        return $this->value;
    }

    #[\Override]
    public function getPhoneNumber(): NullablePhoneNumber
    {
        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
