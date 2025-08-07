<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache\Warmup;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Cache\Warmup\StaticCacheDataStruct;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StaticCacheDataStructTest extends TestCase
{
    #[Test]
    public function happyPath(): void
    {
        $key = new CacheKey('foo');
        $value = new \stdClass();

        $dataStruct = new StaticCacheDataStruct($key, $value);

        self::assertSame($key, $dataStruct->key);
        self::assertSame($value, $dataStruct->value);
    }
}
