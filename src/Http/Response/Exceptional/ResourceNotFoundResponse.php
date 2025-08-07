<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

class ResourceNotFoundResponse extends NotFoundResponse
{
    protected string $title = "Resource Not Found";
    protected string $detail = 'The requested resource could not be found.';
}
