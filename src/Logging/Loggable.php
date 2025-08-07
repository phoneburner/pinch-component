<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Logging;

interface Loggable
{
    public function getLogEntry(): LogEntry;
}
