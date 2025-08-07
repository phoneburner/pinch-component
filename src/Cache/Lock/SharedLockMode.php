<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Lock;

enum SharedLockMode
{
    case Write;
    case Read;
}
