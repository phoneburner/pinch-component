<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
enum PhoneNumberFormat
{
    /**
     * @example "+13145551234"
     */
    case E164;

    /**
     * @example "3145551234"
     */
    case StripPrefix;

    /**
     * @example "(314) 555-1234"
     */
    case National;

    /**
     * @example "+1 314-555-1234"
     */
    case International;

    /**
     * @example "tel:+1-314-555-1234"
     */
    case Rfc3966;
}
