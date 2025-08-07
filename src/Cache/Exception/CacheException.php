<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Exception;

interface CacheException extends \Psr\SimpleCache\CacheException, \Psr\Cache\CacheException
{
}
