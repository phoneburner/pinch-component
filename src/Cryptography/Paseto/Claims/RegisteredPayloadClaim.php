<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims;

use PhoneBurner\Pinch\Enum\Trait\WithValuesStaticMethod;

/**
 * The following keys are reserved for use within PASETO payloads. Users SHOULD NOT write
 * arbitrary/invalid data to any keys in a top-level PASETO payload in the list below.
 *
 * The Expiration, NotBefore, and IssuedAt claims are all date/times and MUST
 * be represented in RFC3339 format with the "T" separator character. They SHOULD
 * have the "Z" UTC time zone offset, and not have a fractional second component.
 */
enum RegisteredPayloadClaim: string
{
    use WithValuesStaticMethod;

    case Issuer = 'iss'; // string
    case Subject = 'sub'; // string
    case Audience = 'aud'; // string
    case Expiration = 'exp'; // RFC3339 with "Z" UTC time zone offset
    case NotBefore = 'nbf'; // RFC3339 with "Z" UTC time zone offset
    case IssuedAt = 'iat'; // RFC3339 with "Z" UTC time zone offset
    case TokenId = 'jti'; // string
}
