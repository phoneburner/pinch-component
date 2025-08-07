<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Exception;

use DomainException;

/**
 * Exception thrown when rate limit configuration is invalid
 *
 * This represents a logic error in rate limit configuration,
 * not a type-related or runtime error.
 */
class InvalidRateLimits extends DomainException
{
}
