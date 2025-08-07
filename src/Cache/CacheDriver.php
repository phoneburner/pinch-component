<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache;

use PhoneBurner\Pinch\Enum\Trait\WithStringBackedInstanceStaticMethod;

enum CacheDriver: string
{
    use WithStringBackedInstanceStaticMethod;

    case File = 'file';
    case Memory = 'memory';
    case None = 'none';
    case Remote = 'remote';
}
