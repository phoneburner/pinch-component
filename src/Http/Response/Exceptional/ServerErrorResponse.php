<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;

class ServerErrorResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::INTERNAL_SERVER_ERROR;
    protected string $title = HttpReasonPhrase::INTERNAL_SERVER_ERROR;
    protected string $detail = 'An internal server error occurred.';
}
