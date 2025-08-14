<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;

class NotFoundResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::NOT_FOUND;
    protected string $title = HttpReasonPhrase::NOT_FOUND;
    protected string $detail = HttpReasonPhrase::NOT_FOUND;
    protected string|null $http_reason_phrase = HttpReasonPhrase::NOT_FOUND;
}
