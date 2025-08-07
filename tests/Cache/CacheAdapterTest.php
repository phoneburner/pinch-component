<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache;

use DateInterval;
use PhoneBurner\Pinch\Component\Cache\Cache;
use PhoneBurner\Pinch\Component\Cache\CacheAdapter;
use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Cache\Exception\CacheWriteFailed;
use PhoneBurner\Pinch\Component\Cache\Psr6\InMemoryCachePool;
use PhoneBurner\Pinch\Time\Clock\StaticClock;
use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

// Added import

final class CacheAdapterTest extends TestCase
{
    private StaticClock $clock;
    private InMemoryCachePool $pool;
    private CacheAdapter $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->clock = new StaticClock('2025-04-05T00:00:00+00:00');
        $this->pool = new InMemoryCachePool($this->clock);
        $this->sut = new CacheAdapter($this->pool);

        self::assertInstanceOf(CacheItemPoolInterface::class, $this->sut);
        self::assertInstanceOf(CacheInterface::class, $this->sut);
        self::assertInstanceOf(Cache::class, $this->sut);
    }

    // == Helper Methods ==

    private function assertItemInPool(
        string|CacheKey $key,
        mixed $expected_value,
        int|null $expected_ttl_seconds = null,
    ): void {
        $normalized_key = $key instanceof CacheKey ? $key->normalized : (new CacheKey($key))->normalized;
        $item = $this->pool->getItem($normalized_key);
        self::assertTrue($item->isHit(), \sprintf("Item '%s' should be a hit in the underlying pool", $normalized_key));
        self::assertSame($expected_value, $item->get(), \sprintf("Item '%s' value mismatch", $normalized_key));

        // Note: PSR-6 doesn't expose expiry time directly, this relies on internal detail of CacheItem used by InMemoryCachePool
        // This is acceptable for testing the adapter's interaction with the pool.
        $reflection = new \ReflectionClass($item);
        $expiry_prop = $reflection->getProperty('expiration');
        /** @var ?\DateTimeImmutable $expiry */
        $expiry = $expiry_prop->getValue($item);

        if ($expected_ttl_seconds !== null) {
            self::assertNotNull($expiry, \sprintf("Item '%s' should have an expiry", $normalized_key));
            $expected_expiry = $this->clock->now()->add(new DateInterval(\sprintf('PT%dS', $expected_ttl_seconds)));
            self::assertEquals($expected_expiry, $expiry, \sprintf("Item '%s' expiry mismatch", $normalized_key));
        } else {
            // Expect expiry to be null (meaning indefinite TTL)
            self::assertNull($expiry, \sprintf("Item '%s' should not have an expiry (null TTL)", $normalized_key));
        }
    }

    private function assertItemNotInPool(string|CacheKey $key): void
    {
        $normalized_key = $key instanceof CacheKey ? $key->normalized : (new CacheKey($key))->normalized;
        self::assertFalse($this->pool->hasItem($normalized_key), \sprintf("Item '%s' should not exist in the underlying pool", $normalized_key));
    }

    // == Data Providers ==

    public static function providesKeys(): \Generator
    {
        yield 'simple string' => ['my_key', 'my_key'];
        yield 'string with spaces' => ['my key', 'my_key'];
        yield 'string with special chars' => ['my:key/{\\\\}@()', 'my_key'];
        yield 'CacheKey object' => [new CacheKey('my:key/{\\\\}@()'), 'my_key'];
        yield 'Stringable object' => [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'my:key/{\\\\}@()';
                }
            },
            'my_key',
        ];
    }

    public static function providesTtls(): \Generator
    {
        yield 'null (indefinite)' => [null, null]; // PSR-16 allows null
        yield 'TimeInterval default (5 minutes)' => [new TimeInterval(seconds: 300), 300]; // Corrected expected seconds
        yield 'TimeInterval specific (10 seconds)' => [new TimeInterval(seconds: 10), 10];
        yield 'TimeInterval max (indefinite)' => [TimeInterval::max(), null];
        yield 'int (30 seconds)' => [30, 30]; // PSR-16 allows int
        yield 'DateInterval (1 minute)' => [new DateInterval('PT1M'), 60]; // PSR-16 allows DateInterval
    }

    // == Cache Interface Tests ==

    #[DataProvider('providesKeys')]
    #[Test]
    public function getRetrievesItemOrReturnsNull(string|\Stringable $key, string $normalized_key): void
    {
        // Test miss
        self::assertNull($this->sut->get($key), 'Should return null for miss');

        // Test hit
        $this->pool->save($this->pool->getItem($normalized_key)->set('test_value'));
        self::assertSame('test_value', $this->sut->get($key), 'Should return value for hit');
    }

    #[Test]
    public function getMultipleRetrievesItemsOrReturnsNull(): void
    {
        $keys = [
            'key1', // Hit
            new CacheKey('key2'), // Hit
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key3';
                }
            }, // Miss
            'key4', // Hit
        ];
        $expected_normalized_keys = ['key1', 'key2', 'key3', 'key4'];

        // Arrange: Save items
        $this->pool->save($this->pool->getItem($expected_normalized_keys[0])->set('value1'));
        $this->pool->save($this->pool->getItem($expected_normalized_keys[1])->set('value2'));
        // key3 is not saved
        $this->pool->save($this->pool->getItem($expected_normalized_keys[3])->set('value4'));

        // Act
        $result = $this->sut->getMultiple($keys);

        // Assert
        self::assertIsIterable($result);
        $result_array = \is_array($result) ? $result : \iterator_to_array($result);

        // Check keys are normalized in the result array
        self::assertArrayHasKey($expected_normalized_keys[0], $result_array);
        self::assertArrayHasKey($expected_normalized_keys[1], $result_array);
        self::assertArrayHasKey($expected_normalized_keys[2], $result_array);
        self::assertArrayHasKey($expected_normalized_keys[3], $result_array);
        self::assertCount(4, $result_array);

        self::assertSame('value1', $result_array[$expected_normalized_keys[0]]);
        self::assertSame('value2', $result_array[$expected_normalized_keys[1]]);
        self::assertNull($result_array[$expected_normalized_keys[2]]); // Miss returns null (default)
        self::assertSame('value4', $result_array[$expected_normalized_keys[3]]);
    }

    #[DataProvider('providesKeys')]
    #[Test]
    public function setStoresItemWithDefaultTtl(string|\Stringable $key, string $normalized_key): void
    {
        $result = $this->sut->set($key, 'test_value');

        self::assertTrue($result);
        $this->assertItemInPool($normalized_key, 'test_value', 300); // Corrected expected seconds
    }

    #[Test]
    public function setMultipleStoresItemsWithDefaultTtl(): void
    {
        $values = [
            'key1' => 'value1',
            (new CacheKey('key2'))->normalized => 'value2', // Use normalized string key
            (string)(new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key3';
                }
            }) => 'value3',
        ];

        $result = $this->sut->setMultiple($values); // Default TTL (5 minutes)

        self::assertTrue($result);
        $this->assertItemInPool('key1', 'value1', 300); // Corrected expected seconds
        $this->assertItemInPool('key2', 'value2', 300); // Corrected expected seconds
        $this->assertItemInPool('key3', 'value3', 300); // Corrected expected seconds
    }

    #[DataProvider('providesKeys')]
    #[Test]
    public function deleteRemovesItem(string|\Stringable $key, string $normalized_key): void
    {
        // Arrange: Add item
        $this->pool->save($this->pool->getItem($normalized_key)->set('value'));
        self::assertTrue($this->pool->hasItem($normalized_key));

        // Act
        $result = $this->sut->delete($key);

        // Assert
        self::assertTrue($result);
        $this->assertItemNotInPool($normalized_key);
    }

    #[Test]
    public function deleteReturnsTrueForNonExistentItem(): void
    {
        self::assertTrue($this->sut->delete('non_existent'));
    }

    #[Test]
    public function deleteMultipleRemovesItems(): void
    {
        $keys = [
            'key1', // Hit
            new CacheKey('key2'), // Hit
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key3';
                }
            }, // Miss
            'key4', // Hit
        ];
        $expected_normalized_keys = ['key1', 'key2', 'key3', 'key4'];

        // Arrange: Save items
        $this->pool->save($this->pool->getItem($expected_normalized_keys[0])->set('value1'));
        $this->pool->save($this->pool->getItem($expected_normalized_keys[1])->set('value2'));
        // key3 is not saved
        $this->pool->save($this->pool->getItem($expected_normalized_keys[3])->set('value4'));

        // Act
        $result = $this->sut->deleteMultiple($keys);

        // Assert
        self::assertTrue($result);
        $this->assertItemNotInPool($expected_normalized_keys[0]);
        $this->assertItemNotInPool($expected_normalized_keys[1]);
        $this->assertItemNotInPool($expected_normalized_keys[2]); // Still not there
        $this->assertItemNotInPool($expected_normalized_keys[3]);
    }

    #[Test]
    public function deleteMultipleReturnsTrueForAllNonExistentItems(): void
    {
        self::assertTrue($this->sut->deleteMultiple(['k1', 'k2']));
    }

    #[Test]
    public function rememberReturnsExistingValue(): void
    {
        $this->sut->set('my_key', 'existing_value', new TimeInterval(seconds: 60));
        $callback_called = false;
        $callback = function () use (&$callback_called): string {
            $callback_called = true;
            return 'new_value';
        };

        $result = $this->sut->remember('my_key', $callback, new TimeInterval(seconds: 10));

        self::assertSame('existing_value', $result);
        self::assertFalse($callback_called, 'Callback should not be called on cache hit');
        // Verify original TTL wasn't changed
        $this->assertItemInPool('my_key', 'existing_value', 60);
    }

    #[Test]
    public function rememberExecutesCallbackAndStoresResultOnMiss(): void
    {
        $callback_called = false;
        $callback = function () use (&$callback_called): string {
            $callback_called = true;
            return 'new_value';
        };

        $result = $this->sut->remember('my_key', $callback, new TimeInterval(seconds: 10));

        self::assertSame('new_value', $result);
        self::assertTrue($callback_called, 'Callback should be called on cache miss');
        $this->assertItemInPool('my_key', 'new_value', 10);
    }

    #[Test]
    public function rememberForcesRefresh(): void
    {
        $this->sut->set('my_key', 'existing_value', new TimeInterval(seconds: 60));
        $callback_called = false;
        $callback = function () use (&$callback_called): string {
            $callback_called = true;
            return 'new_value';
        };

        $result = $this->sut->remember('my_key', $callback, new TimeInterval(seconds: 10), true); // force_refresh = true

        self::assertSame('new_value', $result);
        self::assertTrue($callback_called, 'Callback should be called when force_refresh is true');
        $this->assertItemInPool('my_key', 'new_value', 10);
    }

    #[Test]
    public function rememberHandlesNullCallbackResult(): void
    {
        $callback_called = false;
        $callback = function () use (&$callback_called): string|null {
            $callback_called = true;
            return null;
        };

        $result = $this->sut->remember('my_key', $callback, new TimeInterval(seconds: 10));

        self::assertNull($result);
        self::assertTrue($callback_called, 'Callback should be called');
        // Verify item was NOT stored because callback returned null
        $this->assertItemNotInPool('my_key');
    }

    #[Test]
    public function forgetReturnsValueAndDeletesItem(): void
    {
        $this->sut->set('my_key', 'value_to_forget');
        self::assertTrue($this->pool->hasItem('my_key'));

        $result = $this->sut->forget('my_key');

        self::assertSame('value_to_forget', $result);
        $this->assertItemNotInPool('my_key');
    }

    #[Test]
    public function forgetReturnsNullForNonExistentItem(): void
    {
        $result = $this->sut->forget('non_existent');
        self::assertNull($result);
    }

    #[Test]
    public function getPsr16RespectsDefaultValue(): void
    {
        self::assertSame('default', $this->sut->get('miss_key', 'default'));

        $this->sut->set('hit_key', 'actual_value');
        self::assertSame('actual_value', $this->sut->get('hit_key', 'default'));
    }

    #[DataProvider('providesTtls')]
    #[Test]
    public function setPsr16AcceptsVariousTtlFormats(
        TimeInterval|DateInterval|int|null $ttl_input,
        int|null $expected_seconds,
    ): void {
        $result = $this->sut->set('ttl_key', 'value', $ttl_input);

        self::assertTrue($result);
        $this->assertItemInPool('ttl_key', 'value', $expected_seconds);
    }

    #[Test]
    public function getMultiplePsr16RespectsDefault(): void
    {
        $keys = ['hit1', 'miss1', 'hit2'];
        $this->sut->set('hit1', 'v1');
        $this->sut->set('hit2', 'v2');

        $results = $this->sut->getMultiple($keys, 'DEFAULT');
        $expected = [
            'hit1' => 'v1',
            'miss1' => 'DEFAULT',
            'hit2' => 'v2',
        ];

        self::assertSame($expected, $results);
    }

    #[DataProvider('providesTtls')]
    #[Test]
    public function setMultiplePsr16AcceptsVariousTtlFormats(
        TimeInterval|DateInterval|int|null $ttl_input,
        int|null $expected_seconds,
    ): void {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $result = $this->sut->setMultiple($values, $ttl_input);

        self::assertTrue($result);
        $this->assertItemInPool('key1', 'value1', $expected_seconds);
        $this->assertItemInPool('key2', 'value2', $expected_seconds);
    }

    #[Test]
    public function clearPsr16ClearsPool(): void
    {
        $this->sut->set('key1', 'v1');
        $this->sut->set('key2', 'v2');

        $result = $this->sut->clear();

        self::assertTrue($result);
        $this->assertItemNotInPool('key1');
        $this->assertItemNotInPool('key2');
    }

    #[Test]
    public function hasPsr16ChecksPool(): void
    {
        self::assertFalse($this->sut->has('key1'));
        $this->sut->set('key1', 'v1');
        self::assertTrue($this->sut->has('key1'));
        $this->sut->delete('key1');
        self::assertFalse($this->sut->has('key1'));
    }

    #[DataProvider('providesKeys')]
    #[Test]
    public function getItemPsr6ProxiesAndNormalizes(string|\Stringable $key, string $normalized_key): void
    {
        $this->pool->save($this->pool->getItem($normalized_key)->set('value'));
        $item = $this->sut->getItem($key);
        self::assertInstanceOf(CacheItemInterface::class, $item);
        self::assertTrue($item->isHit());
        self::assertSame('value', $item->get());
        self::assertSame($normalized_key, $item->getKey());
    }

    #[Test]
    public function getItemsPsr6ProxiesAndNormalizes(): void
    {
        $keys = ['key1', new CacheKey('key2'), new class implements \Stringable {
            public function __toString(): string
            {
                return 'key3';
            }
        }];
        $normalized = ['key1', 'key2', 'key3'];
        $this->pool->save($this->pool->getItem($normalized[0])->set('v1'));
        $this->pool->save($this->pool->getItem($normalized[1])->set('v2'));
        // key3 not saved

        $items = $this->sut->getItems($keys);
        self::assertIsIterable($items);
        $items_array = \is_array($items) ? $items : \iterator_to_array($items);

        self::assertCount(3, $items_array); // Pool returns items for all requested normalized keys
        self::assertArrayHasKey($normalized[0], $items_array);
        self::assertArrayHasKey($normalized[1], $items_array);
        self::assertArrayHasKey($normalized[2], $items_array);

        self::assertTrue($items_array[$normalized[0]]->isHit());
        self::assertSame('v1', $items_array[$normalized[0]]->get());
        self::assertTrue($items_array[$normalized[1]]->isHit());
        self::assertSame('v2', $items_array[$normalized[1]]->get());
        self::assertFalse($items_array[$normalized[2]]->isHit());
    }

    #[DataProvider('providesKeys')]
    #[Test]
    public function hasItemPsr6ProxiesAndNormalizes(string|\Stringable $key, string $normalized_key): void
    {
        self::assertFalse($this->sut->hasItem($key));
        $this->pool->save($this->pool->getItem($normalized_key)->set('v'));
        self::assertTrue($this->sut->hasItem($key));
    }

    #[DataProvider('providesKeys')]
    #[Test]
    public function deleteItemPsr6ProxiesAndNormalizes(string|\Stringable $key, string $normalized_key): void
    {
        $this->pool->save($this->pool->getItem($normalized_key)->set('v'));
        self::assertTrue($this->sut->deleteItem($key));
        $this->assertItemNotInPool($normalized_key);
    }

    #[Test]
    public function deleteItemsPsr6ProxiesAndNormalizes(): void
    {
        $keys = ['key1', new CacheKey('key2'), new class implements \Stringable {
            public function __toString(): string
            {
                return 'key3';
            }
        }];
        $normalized = ['key1', 'key2', 'key3'];
        $this->pool->save($this->pool->getItem($normalized[0])->set('v1'));
        $this->pool->save($this->pool->getItem($normalized[1])->set('v2'));

        self::assertTrue($this->sut->deleteItems($keys));
        $this->assertItemNotInPool($normalized[0]);
        $this->assertItemNotInPool($normalized[1]);
        $this->assertItemNotInPool($normalized[2]);
    }

    #[Test]
    public function savePsr6Proxies(): void
    {
        $item = $this->pool->getItem('save_key'); // Use pool directly to get a concrete item
        $item->set('save_value');
        self::assertTrue($this->sut->save($item));
        $this->assertItemInPool('save_key', 'save_value', null); // TTL not set by proxy save
    }

    #[Test]
    public function saveDeferredPsr6Proxies(): void
    {
        $item = $this->pool->getItem('defer_key');
        $item->set('defer_value');
        self::assertTrue($this->sut->saveDeferred($item));
        $this->assertItemNotInPool('defer_key'); // Not saved yet
        self::assertTrue($this->sut->commit()); // Commit proxied
        $this->assertItemInPool('defer_key', 'defer_value', null);
    }

    #[Test]
    public function commitPsr6Proxies(): void
    {
        $item1 = $this->pool->getItem('d1')->set('v1');
        $item2 = $this->pool->getItem('d2')->set('v2');
        $this->sut->saveDeferred($item1);
        $this->sut->saveDeferred($item2);
        $this->assertItemNotInPool('d1');
        $this->assertItemNotInPool('d2');

        self::assertTrue($this->sut->commit());

        $this->assertItemInPool('d1', 'v1');
        $this->assertItemInPool('d2', 'v2');
    }

    #[Test]
    public function rememberThrowsExceptionOnSaveFailure(): void
    {
        $failing_pool = $this->createMock(CacheItemPoolInterface::class);
        $item_mock = $this->createMock(CacheItemInterface::class);

        $failing_pool->method('getItem')->willReturn($item_mock);
        $item_mock->method('isHit')->willReturn(false);
        $item_mock->method('set')->willReturnSelf();
        $item_mock->method('expiresAfter')->willReturnSelf();
        $failing_pool->method('save')->willReturn(false);

        $sut = new CacheAdapter($failing_pool);

        $this->expectException(CacheWriteFailed::class);
        $sut->remember('key', fn(): string => 'value');
    }

    #[Test]
    public function forgetThrowsExceptionOnDeleteFailure(): void
    {
        $failing_pool = $this->createMock(CacheItemPoolInterface::class);
        $item_mock = $this->createMock(CacheItemInterface::class);

        $failing_pool->method('getItem')->willReturn($item_mock);
        $item_mock->method('isHit')->willReturn(true);
        $item_mock->method('get')->willReturn('value');
        $failing_pool->method('deleteItem')->willReturn(false);

        $sut = new CacheAdapter($failing_pool);

        $this->expectException(CacheWriteFailed::class);
        $sut->forget('key');
    }
}
