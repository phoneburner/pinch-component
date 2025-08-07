<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Exception;

class LockAcquisitionTimeout extends \RuntimeException implements CacheException
{
}
