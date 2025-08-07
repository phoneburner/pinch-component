<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

enum Context
{
    case Http;
    case Cli;
    case Test;
}
