<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;

class DeadRouteResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::GONE;
    protected string|null $http_reason_phrase = HttpReasonPhrase::GONE;
    protected string $title = 'No Longer Supported';
    protected string $detail = 'The functionality formerly provided at this address is no longer supported.';
}
