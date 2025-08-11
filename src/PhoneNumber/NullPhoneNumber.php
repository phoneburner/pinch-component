<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumberAware;

/**
 * Value object representing an empty phone number.
 */
#[Contract]
final readonly class NullPhoneNumber implements NullablePhoneNumber, NullablePhoneNumberAware
{
    public static function make(): self
    {
        static $cache = new self();
        return $cache;
    }

    #[\Override]
    public function getPhoneNumber(): self
    {
        return $this;
    }

    #[\Override]
    public function toE164(): E164|null
    {
        return null;
    }
}
