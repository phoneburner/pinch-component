<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Exception;

class InvalidSessionId extends \UnexpectedValueException implements HttpSessionException
{
}
