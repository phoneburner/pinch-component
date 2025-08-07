<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Exception;

class SessionAlreadyStarted extends \LogicException implements HttpSessionException
{
}
