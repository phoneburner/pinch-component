<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use PhoneBurner\Pinch\Component\Http\Domain\HttpReasonPhrase;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;

class NotImplementedResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::NOT_IMPLEMENTED;
    protected string $title = HttpReasonPhrase::NOT_IMPLEMENTED;
    protected string $detail = 'This functionality is not yet implemented.';
}
