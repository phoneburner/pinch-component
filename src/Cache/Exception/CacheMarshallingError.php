<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cache\Exception;

use PhoneBurner\Pinch\Exception\SerializationFailure;

class CacheMarshallingError extends SerializationFailure implements CacheException
{
}
