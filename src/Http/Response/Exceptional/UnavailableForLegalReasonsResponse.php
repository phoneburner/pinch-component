<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;

class UnavailableForLegalReasonsResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::UNAVAILABLE_FOR_LEGAL_REASONS;
    protected string $title = HttpReasonPhrase::UNAVAILABLE_FOR_LEGAL_REASONS;
    protected string $detail = 'Access to the resource is prohibited as a consequence of a legal demand, requirement, or action.';
}
