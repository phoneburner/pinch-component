<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache\AppendOnly;

use PhoneBurner\Pinch\Component\Cache\AppendOnly\AppendOnlyCacheAdapter;
use PhoneBurner\Pinch\Component\Cache\AppendOnlyCache;
use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Cache\Exception\CacheWriteFailed;
use PhoneBurner\Pinch\Component\Cache\Psr6\InMemoryCachePool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

// Note: Testing PSR-16 'has' and 'clear' even though they aren't in AppendOnlyCache interface,
// because AppendOnlyCacheAdapter still implements CacheInterface (PSR-16) for broader compatibility,
// although direct usage of delete/forget methods from that interface is discouraged/will fail.

final class AppendOnlyCacheAdapterTest extends TestCase
{
    private InMemoryCachePool $pool;
    private AppendOnlyCacheAdapter $sut;

    #[\Override]
    protected function setUp(): void
    {
        // No clock needed as AppendOnly doesn't use TTLs
        $this->pool = new InMemoryCachePool();
        $this->sut = new AppendOnlyCacheAdapter($this->pool);

        self::assertInstanceOf(CacheItemPoolInterface::class, $this->sut);
        self::assertInstanceOf(CacheInterface::class, $this->sut); // Implements PSR-16
        self::assertInstanceOf(AppendOnlyCache::class, $this->sut);
    }

    // == Helper Methods ==

    private function assertItemInPool(string|CacheKey $key, mixed $expected_value): void
    {
        $normalized_key = $key instanceof CacheKey ? $key->normalized : (new CacheKey($key))->normalized;
        $item = $this->pool->getItem($normalized_key);
        self::assertTrue($item->isHit(), \sprintf("Item '%s' should be a hit in the underlying pool", $normalized_key));
        self::assertSame($expected_value, $item->get(), \sprintf("Item '%s' value mismatch", $normalized_key));

        // Assert TTL is indefinite (expiration is null)
        $expiry_prop = new \ReflectionClass($item)->getProperty('expiration');
        self::assertNull($expiry_prop->getValue($item), \sprintf("Item '%s' should not have an expiry (AppendOnly)", $normalized_key));
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
        yield 'string with special chars' => ['my:key/{\\}@()', 'my_key'];
        yield 'CacheKey object' => [new CacheKey('my:key/{\\}@()'), 'my_key'];
        yield 'Stringable object' => [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'my:key/{\\}@()';
                }
            },
            'my_key',
        ];
    }

    // == AppendOnlyCache Interface Tests ==

    #[DataProvider('providesKeys')]
    #[Test]
    public function getRetrievesItemOrReturnsNull(string|\Stringable $key, string $normalized_key): void
    {
        // Test miss
        self::assertNull($this->sut->get($key), 'Should return null for miss');

        // Test hit
        $this->pool->save($this->pool->getItem($normalized_key)->set('test_value')->expiresAfter(null)); // Save indefinitely
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
        $this->pool->save($this->pool->getItem($expected_normalized_keys[0])->set('value1')->expiresAfter(null));
        $this->pool->save($this->pool->getItem($expected_normalized_keys[1])->set('value2')->expiresAfter(null));
        // key3 is not saved
        $this->pool->save($this->pool->getItem($expected_normalized_keys[3])->set('value4')->expiresAfter(null));

        // Act
        $result = $this->sut->getMultiple($keys);

        // Assert
        self::assertIsIterable($result);
        $result_array = \is_array($result) ? $result : \iterator_to_array($result);

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
    public function setStoresItemIndefinitely(string|\Stringable $key, string $normalized_key): void
    {
        $result = $this->sut->set($key, 'test_value'); // TTL omitted/ignored

        self::assertTrue($result);
        $this->assertItemInPool($normalized_key, 'test_value'); // Asserts indefinite TTL
    }

    #[Test]
    public function setMultipleStoresItemsIndefinitely(): void
    {
        $values = [
            'key1' => 'value1',
            (new CacheKey('key2'))->normalized => 'value2',
            (string)(new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key3';
                }
            }) => 'value3',
        ];

        $result = $this->sut->setMultiple($values); // TTL omitted/ignored

        self::assertTrue($result);
        $this->assertItemInPool('key1', 'value1');
        $this->assertItemInPool('key2', 'value2');
        $this->assertItemInPool('key3', 'value3');
    }

    #[Test]
    public function rememberReturnsExistingValue(): void
    {
        $this->sut->set('my_key', 'existing_value');
        $callback_called = false;
        $callback = function () use (&$callback_called): string {
            $callback_called = true;
            return 'new_value';
        };

        $result = $this->sut->remember('my_key', $callback);

        self::assertSame('existing_value', $result);
        self::assertFalse($callback_called, 'Callback should not be called on cache hit');
        $this->assertItemInPool('my_key', 'existing_value'); // Still indefinite
    }

    #[Test]
    public function rememberExecutesCallbackAndStoresResultOnMiss(): void
    {
        $callback_called = false;
        $callback = function () use (&$callback_called): string {
            $callback_called = true;
            return 'new_value';
        };

        $result = $this->sut->remember('my_key', $callback);

        self::assertSame('new_value', $result);
        self::assertTrue($callback_called, 'Callback should be called on cache miss');
        $this->assertItemInPool('my_key', 'new_value'); // Stored indefinitely
    }

    #[Test]
    public function rememberHandlesNullCallbackResult(): void
    {
        $callback_called = false;
        $callback = function () use (&$callback_called): string|null {
            $callback_called = true;
            return null;
        };

        $result = $this->sut->remember('my_key', $callback);

        self::assertNull($result);
        self::assertTrue($callback_called, 'Callback should be called');
        $this->assertItemNotInPool('my_key'); // Not stored
    }

    // == CacheInterface (PSR-16) Tests (subset relevant to AppendOnly) ==

    #[Test]
    public function getPsr16RespectsDefaultValue(): void
    {
        self::assertSame('default', $this->sut->get('miss_key', 'default'));
        $this->sut->set('hit_key', 'actual_value');
        self::assertSame('actual_value', $this->sut->get('hit_key', 'default'));
    }

    #[Test]
    public function setPsr16AcceptsVariousTtlFormatsButStoresIndefinitely(): void
    {
        // Test with different TTL inputs allowed by PSR-16, ensure they are ignored
        $ttl_inputs = [null, new \DateInterval('PT1H'), 3600];
        foreach ($ttl_inputs as $i => $ttl_input) {
            $key = 'ttl_key_' . $i;
            $result = $this->sut->set($key, 'value', $ttl_input);
            self::assertTrue($result);
            $this->assertItemInPool($key, 'value'); // Always asserts indefinite TTL
        }
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
        self::assertSame($expected, \iterator_to_array($results)); // Convert iterable
    }

    #[Test]
    public function setMultiplePsr16AcceptsVariousTtlFormatsButStoresIndefinitely(): void
    {
        $values = ['key1' => 'value1', 'key2' => 'value2'];
        $ttl_inputs = [null, new \DateInterval('PT1H'), 3600];

        foreach ($ttl_inputs as $i => $ttl_input) {
            // Clear pool before each TTL type test to avoid key collisions if needed
            $this->pool->clear();
            $current_values = [];
            foreach ($values as $k => $v) {
                $current_values[$k . '_' . $i] = $v;
            }

            $result = $this->sut->setMultiple($current_values, $ttl_input);
            self::assertTrue($result);

            foreach ($current_values as $k => $v) {
                $this->assertItemInPool($k, $v); // Always asserts indefinite TTL
            }
        }
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
    }

    // == CacheItemPoolInterface (PSR-6) Tests (subset relevant to AppendOnly) ==

    #[DataProvider('providesKeys')]
    #[Test]
    public function getItemPsr6ProxiesAndNormalizes(string|\Stringable $key, string $normalized_key): void
    {
        $this->pool->save($this->pool->getItem($normalized_key)->set('value')->expiresAfter(null));
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
        $this->pool->save($this->pool->getItem($normalized[0])->set('v1')->expiresAfter(null));
        $this->pool->save($this->pool->getItem($normalized[1])->set('v2')->expiresAfter(null));
        // key3 not saved

        $items = $this->sut->getItems($keys);
        self::assertIsIterable($items);
        $items_array = \is_array($items) ? $items : \iterator_to_array($items);

        self::assertCount(3, $items_array);
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
        $this->pool->save($this->pool->getItem($normalized_key)->set('v')->expiresAfter(null));
        self::assertTrue($this->sut->hasItem($key));
    }

    #[Test]
    public function savePsr6Proxies(): void
    {
        $item = $this->pool->getItem('save_key');
        $item->set('save_value'); // Intentionally don't set expiresAfter(null) here
        self::assertTrue($this->sut->save($item));
        $this->assertItemInPool('save_key', 'save_value'); // Should be saved indefinitely by adapter
    }

    #[Test]
    public function saveDeferredPsr6Proxies(): void
    {
        $item = $this->pool->getItem('defer_key');
        $item->set('defer_value');
        self::assertTrue($this->sut->saveDeferred($item));
        $this->assertItemNotInPool('defer_key');
        self::assertTrue($this->sut->commit());
        $this->assertItemInPool('defer_key', 'defer_value');
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

    // == Exception Tests ==

    #[Test]
    public function deleteThrowsException(): void
    {
        $this->expectException(CacheWriteFailed::class);
        $this->expectExceptionMessage('AppendOnlyCache does not support delete operations');
        $this->sut->delete('any_key');
    }

    #[Test]
    public function deleteMultipleThrowsException(): void
    {
        $this->expectException(CacheWriteFailed::class);
        $this->expectExceptionMessage('AppendOnlyCache does not support delete operations');
        $this->sut->deleteMultiple(['key1', 'key2']);
    }

    #[Test]
    public function forgetThrowsException(): void
    {
        $this->expectException(CacheWriteFailed::class);
        $this->expectExceptionMessage('AppendOnlyCache does not support delete operations');
        $this->sut->forget('any_key');
    }

    #[Test]
    public function deleteItemPsr6ThrowsException(): void
    {
        $this->expectException(CacheWriteFailed::class);
        $this->expectExceptionMessage('AppendOnlyCache does not support delete operations');
        $this->sut->deleteItem('any_key');
    }

    #[Test]
    public function deleteItemsPsr6ThrowsException(): void
    {
        $this->expectException(CacheWriteFailed::class);
        $this->expectExceptionMessage('AppendOnlyCache does not support delete operations');
        $this->sut->deleteItems(['key1', 'key2']);
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
        $failing_pool->method('save')->willReturn(false); // Simulate failure

        $sut = new AppendOnlyCacheAdapter($failing_pool);

        $this->expectException(CacheWriteFailed::class);
        $this->expectExceptionMessage('set: key'); // Message from remember
        $sut->remember('key', static fn(): string => 'value');
    }
}
