<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Cache\Psr6\CacheItemPoolProxy;
use PhoneBurner\Pinch\Component\Cache\Psr6\InMemoryCachePool;
use PhoneBurner\Pinch\Time\Clock\StaticClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;

final class CacheItemPoolProxyTest extends TestCase
{
    private InMemoryCachePool $underlying_pool;
    private CacheItemPoolProxy $sut_namespaced;
    private CacheItemPoolProxy $sut_no_namespace;

    #[\Override]
    protected function setUp(): void
    {
        $clock = new StaticClock('2025-04-05T00:00:00+00:00');
        $this->underlying_pool = new InMemoryCachePool($clock);
        $this->sut_namespaced = new CacheItemPoolProxy($this->underlying_pool, 'test_ns');
        $this->sut_no_namespace = new CacheItemPoolProxy($this->underlying_pool);
    }

    // Helper to get the expected namespaced key
    private function namespacedKey(string $key): string
    {
        return 'test_ns.' . $key;
    }

    #[DataProvider('providesRawStringKeys')]
    #[Test]
    public function getItemFetchesItemWithNamespace(string $raw_key): void
    {
        $namespaced_key = $this->namespacedKey($raw_key);

        // Arrange: Save item directly in underlying pool with namespaced key
        $item = $this->underlying_pool->getItem($namespaced_key);
        $item->set('test_value');
        $this->underlying_pool->save($item);

        // Act: Fetch item through namespaced proxy using raw key
        $fetched_item = $this->sut_namespaced->getItem($raw_key);

        // Assert
        self::assertInstanceOf(CacheItemInterface::class, $fetched_item);
        self::assertTrue($fetched_item->isHit());
        self::assertSame('test_value', $fetched_item->get());
        // The key returned by the item is the normalized version of the namespaced key
        $expected_normalized_key = (new CacheKey($namespaced_key))->normalized;
        self::assertSame($expected_normalized_key, $fetched_item->getKey());
    }

    #[DataProvider('providesRawStringKeys')]
    #[Test]
    public function getItemFetchesItemWithoutNamespace(string $raw_key): void
    {
        // Arrange: Save item directly in underlying pool with raw key
        $item = $this->underlying_pool->getItem($raw_key);
        $item->set('test_value');
        $this->underlying_pool->save($item);

        // Act: Fetch item through non-namespaced proxy using raw key
        $fetched_item = $this->sut_no_namespace->getItem($raw_key);

        // Assert
        self::assertInstanceOf(CacheItemInterface::class, $fetched_item);
        self::assertTrue($fetched_item->isHit());
        self::assertSame('test_value', $fetched_item->get());
        // The key returned by the item is the normalized version of the raw key
        $expected_normalized_key = (new CacheKey($raw_key))->normalized;
        self::assertSame($expected_normalized_key, $fetched_item->getKey());
    }

    #[Test]
    public function getItemReturnsMissWhenItemNotFoundNamespaced(): void
    {
        $item = $this->sut_namespaced->getItem('non_existent_key');

        self::assertInstanceOf(CacheItemInterface::class, $item);
        self::assertFalse($item->isHit());
        // Key should reflect the namespaced attempt
        self::assertSame($this->namespacedKey('non_existent_key'), $item->getKey());
        self::assertNull($item->get());
    }

    #[Test]
    public function getItemReturnsMissWhenItemNotFoundNoNamespace(): void
    {
        $item = $this->sut_no_namespace->getItem('non_existent_key');

        self::assertInstanceOf(CacheItemInterface::class, $item);
        self::assertFalse($item->isHit());
        self::assertSame('non_existent_key', $item->getKey());
        self::assertNull($item->get());
    }

    #[Test]
    public function getItemsFetchesMultipleItemsNamespaced(): void
    {
        // Arrange: Save items directly in underlying pool with namespaced keys
        $keys = ['key1', 'key2', 'non_existent'];
        $values = ['value1', 'value2', null];

        $item1 = $this->underlying_pool->getItem($this->namespacedKey('key1'));
        $item1->set($values[0]);
        $this->underlying_pool->save($item1);

        $item2 = $this->underlying_pool->getItem($this->namespacedKey('key2'));
        $item2->set($values[1]);
        $this->underlying_pool->save($item2);

        // Act: Fetch items through namespaced proxy
        $fetched_items = $this->sut_namespaced->getItems($keys);

        // Assert
        self::assertIsArray($fetched_items);
        $fetched_items_array = \iterator_to_array($fetched_items); // Convert iterable to array for easier assertion
        self::assertCount(3, $fetched_items_array);

        foreach ($keys as $index => $key) {
            $namespaced_key = $this->namespacedKey($key);
            self::assertArrayHasKey($key, $fetched_items_array);
            $item = $fetched_items_array[$key];
            self::assertInstanceOf(CacheItemInterface::class, $item);
            self::assertSame($namespaced_key, $item->getKey());
            if ($values[$index] !== null) {
                self::assertTrue($item->isHit());
                self::assertSame($values[$index], $item->get());
            } else {
                self::assertFalse($item->isHit());
                self::assertNull($item->get());
            }
        }
    }

    #[Test]
    public function getItemsFetchesMultipleItemsNoNamespace(): void
    {
        // Arrange: Save items directly in underlying pool with raw keys
        $keys = ['key1', 'key2', 'non_existent'];
        $values = ['value1', 'value2', null];

        $item1 = $this->underlying_pool->getItem('key1');
        $item1->set($values[0]);
        $this->underlying_pool->save($item1);
        $item2 = $this->underlying_pool->getItem('key2');
        $item2->set($values[1]);
        $this->underlying_pool->save($item2);

        // Act: Fetch items through non-namespaced proxy
        $fetched_items = $this->sut_no_namespace->getItems($keys);

        // Assert
        self::assertIsArray($fetched_items);
        $fetched_items_array = \iterator_to_array($fetched_items);
        self::assertCount(3, $fetched_items_array);

        foreach ($keys as $index => $key) {
            self::assertArrayHasKey($key, $fetched_items_array);
            $item = $fetched_items_array[$key];
            self::assertInstanceOf(CacheItemInterface::class, $item);
            self::assertSame($key, $item->getKey());
            if ($values[$index] !== null) {
                self::assertTrue($item->isHit());
                self::assertSame($values[$index], $item->get());
            } else {
                self::assertFalse($item->isHit());
                self::assertNull($item->get());
            }
        }
    }

    #[Test]
    public function hasItemChecksUnderlyingPoolNamespaced(): void
    {
        // Arrange: Save item directly in underlying pool
        $this->underlying_pool->save($this->underlying_pool->getItem($this->namespacedKey('existing'))->set('v'));

        // Assert via proxy
        self::assertTrue($this->sut_namespaced->hasItem('existing'));
        self::assertFalse($this->sut_namespaced->hasItem('non_existent'));
        // Verify underlying pool directly
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey('existing')));
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('non_existent')));
        // Check raw key is not present in underlying
        self::assertFalse($this->underlying_pool->hasItem('existing'));
    }

    #[Test]
    public function hasItemChecksUnderlyingPoolNoNamespace(): void
    {
        // Arrange: Save item directly in underlying pool
        $this->underlying_pool->save($this->underlying_pool->getItem('existing')->set('v'));

        // Assert via proxy
        self::assertTrue($this->sut_no_namespace->hasItem('existing'));
        self::assertFalse($this->sut_no_namespace->hasItem('non_existent'));
        // Verify underlying pool directly
        self::assertTrue($this->underlying_pool->hasItem('existing'));
        self::assertFalse($this->underlying_pool->hasItem('non_existent'));
        // Check namespaced key is not present
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('existing')));
    }

    #[Test]
    public function clearCallsUnderlyingPoolClear(): void
    {
        // Arrange: Save items via both proxies
        $this->sut_namespaced->save($this->sut_namespaced->getItem('key1')->set('v1'));
        $this->sut_no_namespace->save($this->sut_no_namespace->getItem('key2')->set('v2'));
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey('key1')));
        self::assertTrue($this->underlying_pool->hasItem('key2'));

        // Act: Clear using one proxy (should clear the whole underlying pool)
        $result = $this->sut_namespaced->clear();

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('key1')));
        self::assertFalse($this->underlying_pool->hasItem('key2'));
        self::assertFalse($this->sut_namespaced->hasItem('key1'));
        self::assertFalse($this->sut_no_namespace->hasItem('key2'));
    }

    #[Test]
    public function deleteItemDeletesNamespacedItem(): void
    {
        // Arrange: Save item via namespaced proxy
        $this->sut_namespaced->save($this->sut_namespaced->getItem('delete_me')->set('v'));
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey('delete_me')));

        // Act: Delete via namespaced proxy
        $result = $this->sut_namespaced->deleteItem('delete_me');

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('delete_me')));
        self::assertFalse($this->sut_namespaced->hasItem('delete_me'));
    }

    #[Test]
    public function deleteItemDeletesNonNamespacedItem(): void
    {
        // Arrange: Save item via non-namespaced proxy
        $this->sut_no_namespace->save($this->sut_no_namespace->getItem('delete_me')->set('v'));
        self::assertTrue($this->underlying_pool->hasItem('delete_me'));

        // Act: Delete via non-namespaced proxy
        $result = $this->sut_no_namespace->deleteItem('delete_me');

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem('delete_me'));
        self::assertFalse($this->sut_no_namespace->hasItem('delete_me'));
    }

    #[Test]
    public function deleteItemsDeletesNamespacedItems(): void
    {
        // Arrange: Save items via namespaced proxy
        $keys = ['key1', 'key2', 'key3'];
        $this->sut_namespaced->save($this->sut_namespaced->getItem($keys[0])->set('v1'));
        $this->sut_namespaced->save($this->sut_namespaced->getItem($keys[1])->set('v2'));
        $this->sut_namespaced->save($this->sut_namespaced->getItem($keys[2])->set('v3'));
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey($keys[0])));
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey($keys[1])));
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey($keys[2])));

        $keys_to_delete = [$keys[0], $keys[2], 'non_existent'];

        // Act: Delete via namespaced proxy
        $result = $this->sut_namespaced->deleteItems($keys_to_delete);

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey($keys[0]))); // Deleted
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey($keys[1]))); // Not deleted
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey($keys[2]))); // Deleted
    }

    #[Test]
    public function deleteItemsDeletesNonNamespacedItems(): void
    {
        // Arrange: Save items via non-namespaced proxy
        $keys = ['key1', 'key2', 'key3'];
        $this->sut_no_namespace->save($this->sut_no_namespace->getItem($keys[0])->set('v1'));
        $this->sut_no_namespace->save($this->sut_no_namespace->getItem($keys[1])->set('v2'));
        $this->sut_no_namespace->save($this->sut_no_namespace->getItem($keys[2])->set('v3'));
        self::assertTrue($this->underlying_pool->hasItem($keys[0]));
        self::assertTrue($this->underlying_pool->hasItem($keys[1]));
        self::assertTrue($this->underlying_pool->hasItem($keys[2]));

        $keys_to_delete = [$keys[0], $keys[2], 'non_existent'];

        // Act: Delete via non-namespaced proxy
        $result = $this->sut_no_namespace->deleteItems($keys_to_delete);

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem($keys[0])); // Deleted
        self::assertTrue($this->underlying_pool->hasItem($keys[1])); // Not deleted
        self::assertFalse($this->underlying_pool->hasItem($keys[2])); // Deleted
    }

    #[Test]
    public function savePersistsItemViaProxyNamespaced(): void
    {
        // Arrange: Get item via proxy
        $item = $this->sut_namespaced->getItem('new_item_key');
        $item->set('new_value');

        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('new_item_key')));

        // Act: Save via proxy
        $result = $this->sut_namespaced->save($item);

        // Assert
        self::assertTrue($result);
        // Check underlying pool has the namespaced key
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey('new_item_key')));

        // Fetch directly from underlying pool to verify
        $fetched_direct = $this->underlying_pool->getItem($this->namespacedKey('new_item_key'));
        self::assertTrue($fetched_direct->isHit());
        self::assertSame('new_value', $fetched_direct->get());
    }

    #[Test]
    public function savePersistsItemViaProxyNoNamespace(): void
    {
        // Arrange: Get item via proxy
        $item = $this->sut_no_namespace->getItem('new_item_key');
        $item->set('new_value');

        self::assertFalse($this->underlying_pool->hasItem('new_item_key'));

        // Act: Save via proxy
        $result = $this->sut_no_namespace->save($item);

        // Assert
        self::assertTrue($result);
        // Check underlying pool has the raw key
        self::assertTrue($this->underlying_pool->hasItem('new_item_key'));

        $fetched_direct = $this->underlying_pool->getItem('new_item_key');
        self::assertTrue($fetched_direct->isHit());
        self::assertSame('new_value', $fetched_direct->get());
    }

    #[Test]
    public function saveDeferredQueuesItemViaProxyNamespaced(): void
    {
        // Arrange
        $item = $this->sut_namespaced->getItem('deferred_key');
        $item->set('deferred_value');

        // Act
        $result = $this->sut_namespaced->saveDeferred($item);

        // Assert: Proxy returns true, underlying pool should have deferred item
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('deferred_key'))); // Not saved yet

        // Check underlying pool's deferred state (requires peeking or commit check)
        $commit_result = $this->underlying_pool->commit();
        self::assertTrue($commit_result);
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey('deferred_key')));
        $fetched_direct = $this->underlying_pool->getItem($this->namespacedKey('deferred_key'));
        self::assertSame('deferred_value', $fetched_direct->get());
    }

    #[Test]
    public function saveDeferredQueuesItemViaProxyNoNamespace(): void
    {
        // Arrange
        $item = $this->sut_no_namespace->getItem('deferred_key');
        $item->set('deferred_value');

        // Act
        $result = $this->sut_no_namespace->saveDeferred($item);

        // Assert: Proxy returns true, underlying pool should have deferred item
        self::assertTrue($result);
        self::assertFalse($this->underlying_pool->hasItem('deferred_key')); // Not saved yet

        $commit_result = $this->underlying_pool->commit();
        self::assertTrue($commit_result);
        self::assertTrue($this->underlying_pool->hasItem('deferred_key'));
        $fetched_direct = $this->underlying_pool->getItem('deferred_key');
        self::assertSame('deferred_value', $fetched_direct->get());
    }

    #[Test]
    public function commitCallsUnderlyingPoolCommit(): void
    {
        // Arrange: Defer items via both proxies
        $item_ns = $this->sut_namespaced->getItem('deferred_ns');
        $item_ns->set('value_ns');
        $this->sut_namespaced->saveDeferred($item_ns);

        $item_no_ns = $this->sut_no_namespace->getItem('deferred_no_ns');
        $item_no_ns->set('value_no_ns');
        $this->sut_no_namespace->saveDeferred($item_no_ns);

        self::assertFalse($this->underlying_pool->hasItem($this->namespacedKey('deferred_ns')));
        self::assertFalse($this->underlying_pool->hasItem('deferred_no_ns'));

        // Act: Commit using one proxy (should commit all in underlying pool)
        $result = $this->sut_namespaced->commit();

        // Assert
        self::assertTrue($result);
        self::assertTrue($this->underlying_pool->hasItem($this->namespacedKey('deferred_ns')));
        self::assertTrue($this->underlying_pool->hasItem('deferred_no_ns'));
        self::assertSame('value_ns', $this->underlying_pool->getItem($this->namespacedKey('deferred_ns'))->get());
        self::assertSame('value_no_ns', $this->underlying_pool->getItem('deferred_no_ns')->get());
    }

    /**
     * Data provider for raw string keys (CacheItemPoolProxy methods expect strings).
     */
    public static function providesRawStringKeys(): \Generator
    {
        yield ['key'];
        yield ['key_with_underscore'];
        yield ['key.with.dots'];
        yield ['key with spaces'];
        yield ['key:with:colons'];
        yield ['key{with}braces'];
        yield ['key(with)parens'];
        yield ['key/with/slashes'];
        yield ['key@with@at'];
        yield ['key\\with\\backslashes'];
        yield ['key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes'];
        yield [CacheKey::class . ':1234'];
    }
}
