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

        $data_struct = new StaticCacheDataStruct($key, $value);

        self::assertSame($key, $data_struct->key);
        self::assertSame($value, $data_struct->value);
    }
}
