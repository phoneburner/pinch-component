<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Exception;

class SessionNotStarted extends \LogicException implements HttpSessionException
{
}
