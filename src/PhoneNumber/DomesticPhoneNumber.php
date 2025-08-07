<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodeAware;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;
use PhoneBurner\Pinch\Component\PhoneNumber\Exception\InvalidPhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\NullablePhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumberFormat;

/**
 * Value object representing a *valid* 10-digit NANP phone number.
 */
#[Contract]
final readonly class DomesticPhoneNumber implements
    PhoneNumber,
    AreaCodeAware,
    \Stringable,
    \JsonSerializable
{
    public AreaCode $area_code;

    private function __construct(public E164 $e164)
    {
        if (! \preg_match(E164::NANP_REGEX, (string)$this->e164)) {
            throw new InvalidPhoneNumber('Not a Valid Domestic Number: ' . $e164);
        }

        $this->area_code = AreaCode::make($this->npa());
    }

    public static function make(NullablePhoneNumber|\Stringable|string|int $phone_number): self
    {
        return $phone_number instanceof self ? $phone_number : new self(E164::make($phone_number));
    }

    public static function tryFrom(mixed $phone_number): self|null
    {
        try {
            return match (true) {
                $phone_number instanceof self, $phone_number === null => $phone_number,
                $phone_number instanceof E164 => new self($phone_number),
                $phone_number instanceof NullablePhoneNumber => self::tryFrom($phone_number->toE164()),
                \is_string($phone_number) => new self(new E164($phone_number)),
                \is_int($phone_number), $phone_number instanceof \Stringable => new self(new E164((string)$phone_number)),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    #[\Override]
    public function toE164(): E164
    {
        return $this->e164;
    }

    public function format(PhoneNumberFormat|null $format = null): string
    {
        return match ($format ?? PhoneNumberFormat::National) {
            PhoneNumberFormat::National => \sprintf("(%s) %s-%s", $this->npa(), $this->nxx(), $this->line()),
            PhoneNumberFormat::StripPrefix => \substr((string)$this->e164, 2),
            PhoneNumberFormat::E164 => (string)$this->e164,
            PhoneNumberFormat::International => \sprintf("+1 %s-%s-%s", $this->npa(), $this->nxx(), $this->line()),
            PhoneNumberFormat::Rfc3966 => \sprintf("tel:+1-%s-%s-%s", $this->npa(), $this->nxx(), $this->line()),
        };
    }

    #[\Override]
    public function getAreaCode(): AreaCode
    {
        return $this->area_code;
    }

    /**
     * Loose equality check for phone numbers. If both are valid domestic phone
     * numbers and the e164 formated string is the same, they are considered equal.
     */
    public function equals(mixed $other): bool
    {
        return $this->e164->value === self::tryFrom($other)?->e164->value;
    }

    /**
     * Returns the National Plan Area (Area Code) part of the NANP phone number.
     */
    public function npa(): string
    {
        return \substr((string)$this->e164, 2, 3);
    }

    /**
     * Returns the Central Office (Exchange) Code part of the NANP phone number.
     */
    public function nxx(): string
    {
        return \substr((string)$this->e164, 5, 3);
    }

    /**
     * Returns the subscriber Line Code (last 4 digits) portion of the NANP phone number.
     */
    public function line(): string
    {
        return \substr((string)$this->e164, 8);
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    public function __serialize(): array
    {
        return ['phone_number' => (string)$this->e164];
    }

    /**
     * @param array{phone_number: string} $data
     */
    public function __unserialize(array $data): void
    {
        $this->__construct(E164::make($data['phone_number']));
    }
}
