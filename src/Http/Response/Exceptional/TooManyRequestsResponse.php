<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;

class TooManyRequestsResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::TOO_MANY_REQUESTS;
    protected string $title = HttpReasonPhrase::TOO_MANY_REQUESTS;
}
