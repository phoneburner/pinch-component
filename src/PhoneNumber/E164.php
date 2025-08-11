<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\Exception\InvalidPhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumberAware;

/**
 * This is the lowest level phone number object we should be working with. It
 * represents a *possible* number in e.164 format. Other services classes or
 * wrapping value objects should deal with the validity of the phone number.
 */
#[Contract]
final readonly class E164 implements
    \Stringable,
    \JsonSerializable,
    PhoneNumber,
    PhoneNumberAware
{
    public const string INTL_REGEX = '/^\+[2-9]\d{6,14}$/';

    public const string NANP_REGEX = '/^\+1[2-9]\d{2}[2-9]\d{2}\d{4}$/';

    /**
     * @var non-empty-string
     */
    public string $value;

    public function __construct(string $value)
    {
        $this->value = self::filter($value) ?? throw new InvalidPhoneNumber(
            'Invalid E164 Phone Number',
        );
    }

    /**
     * @return non-empty-string|null
     */
    private static function filter(string $value): string|null
    {
        if ($value === '') {
            return null;
        }

        // Optimize for the most common case: a NANP number already in e164 format
        if (\preg_match(self::NANP_REGEX, $value)) {
            return $value;
        }

        // Then check the next most common cases using a jump-table optimized match expression
        $phone_number = match (\strlen($value)) {
            10 => \preg_match('/^[2-9]\d{2}[2-9]\d{2}\d{4}$/', $value) ? '+1' . $value : null,
            11 => \preg_match('/^1[2-9]\d{2}[2-9]\d{2}\d{4}$/', $value) ? '+' . $value : null,
            12 => \preg_match('/^([2-9]\d{2})-([2-9]\d{2})-(\d{4}$)/', $value, $m)
                ? \sprintf("+1%s%s%s", $m[1], $m[2], $m[3])
                : null,
            14 => \preg_match('/^\(([2-9]\d{2})\) ([2-9]\d{2})-(\d{4}$)/', $value, $m)
                ? \sprintf("+1%s%s%s", $m[1], $m[2], $m[3])
                : null,
            default => null,
        };

        // If the quick filter check was successful, return the phone number.
        if ($phone_number !== null) {
            return $phone_number;
        }

        // Remove all non-digits from the string
        $value = (string)\preg_replace('/\D/', '', $value);

        // Assume 10-digit numbers that match the NANP pattern are US numbers
        if (\strlen($value) === 10 && \preg_match(self::NANP_REGEX, '+1' . $value)) {
            return '+1' . $value;
        }

        // If the first digit is a "1", it has to be a NANP Number
        if (\str_starts_with($value, '1') && \preg_match(self::NANP_REGEX, '+' . $value)) {
            return '+' . $value;
        }

        return \preg_match(self::INTL_REGEX, '+' . $value) ? '+' . $value : null;
    }

    public static function make(NullablePhoneNumber|\Stringable|string|int $phone_number): self
    {
        return match (true) {
            $phone_number instanceof self => $phone_number,
            $phone_number instanceof NullablePhoneNumber => $phone_number->toE164() ?? throw new InvalidPhoneNumber('Invalid E164 Phone Number'),
            default => new self((string)$phone_number),
        };
    }

    public static function tryFrom(mixed $phone_number): self|null
    {
        try {
            return match (true) {
                $phone_number instanceof self, $phone_number === null => $phone_number,
                $phone_number instanceof NullablePhoneNumber => self::tryFrom($phone_number->toE164()),
                \is_string($phone_number) => new self($phone_number),
                \is_int($phone_number), $phone_number instanceof \Stringable => new self((string)$phone_number),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Loose equality check for phone numbers. If both are valid and the e164
     * formated string is the same, they are considered equal.
     */
    public function equals(mixed $other): bool
    {
        return $this->value === self::tryFrom($other)?->value;
    }

    #[\Override]
    public function getPhoneNumber(): self
    {
        return $this;
    }

    #[\Override]
    public function toE164(): self
    {
        return $this;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->value;
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

    public function __unserialize(array $data): void
    {
        $this->value = $data['value'] ?? $data['phone_number'];
    }
}
