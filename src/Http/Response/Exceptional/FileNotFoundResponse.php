<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

class FileNotFoundResponse extends NotFoundResponse
{
    protected string $title = "File Not Found";
    protected string $detail = 'The file requested could not be found.';
}
