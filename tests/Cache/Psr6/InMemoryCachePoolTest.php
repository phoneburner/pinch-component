<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Cache\Psr6\CacheItem;
use PhoneBurner\Pinch\Component\Cache\Psr6\InMemoryCachePool;
use PhoneBurner\Pinch\Time\Clock\StaticClock;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;

final class InMemoryCachePoolTest extends TestCase
{
    private StaticClock $clock;

    private InMemoryCachePool $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->clock = new StaticClock('2025-04-05T00:00:00+00:00');
        $this->sut = new InMemoryCachePool($this->clock);
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function getItemFetchesItemFromCache(string|\Stringable $raw_key, string $normalized_key): void
    {
        $item = $this->sut->getItem($raw_key);
        $item->set('test_value');
        $this->sut->save($item);

        $fetched_item = $this->sut->getItem($raw_key);

        self::assertInstanceOf(CacheItemInterface::class, $fetched_item);
        self::assertTrue($fetched_item->isHit());
        self::assertSame('test_value', $fetched_item->get());
        self::assertSame($normalized_key, $fetched_item->getKey());
    }

    #[Test]
    public function getItemReturnsMissWhenItemNotFound(): void
    {
        $item = $this->sut->getItem('non_existent_key');

        self::assertInstanceOf(CacheItemInterface::class, $item);
        self::assertFalse($item->isHit());
        self::assertSame('non_existent_key', $item->getKey());
        self::assertNull($item->get());
    }

    #[Test]
    public function getItemsFetchesMultipleItemsCorrectly(): void
    {
        // Arrange: Save some items
        $item1 = $this->sut->getItem('key1');
        $item1->set('value1');
        $this->sut->save($item1);

        $item2 = $this->sut->getItem('key2');
        $item2->set('value2');
        $this->sut->save($item2);

        $key3_obj = new CacheKey('key3');
        $item3 = $this->sut->getItem($key3_obj);
        $item3->set('value3');
        $this->sut->save($item3);

        $stringable_key = new class implements \Stringable {
            public function __toString(): string
            {
                return 'key1';
            }
        };

        $keys_to_fetch = [
            'key1' => 'key1',
            'key2_cachekey' => new CacheKey('key2'),
            'non_existent' => 'non_existent',
            'key1_stringable' => $stringable_key,
        ];

        // Act: Fetch items
        $fetched_items = $this->sut->getItems(\array_values($keys_to_fetch));

        // Assert: Check fetched items
        self::assertCount(3, $fetched_items); // Expect 3 because 'key1' string and Stringable('key1') collapse

        // Check items by the keys provided in the input array
        self::assertArrayHasKey('key1', $fetched_items);
        self::assertTrue($fetched_items['key1']->isHit());
        self::assertSame('value1', $fetched_items['key1']->get());

        // Note: PSR-6 getItems keys the output array by the input string/Stringable representation
        self::assertArrayHasKey((string)new CacheKey('key2'), $fetched_items); // Key will be 'key2'
        $key2_string = (string)new CacheKey('key2');
        self::assertTrue($fetched_items[$key2_string]->isHit());
        self::assertSame('value2', $fetched_items[$key2_string]->get());

        self::assertArrayHasKey('non_existent', $fetched_items);
        self::assertFalse($fetched_items['non_existent']->isHit());

        self::assertArrayHasKey((string)$stringable_key, $fetched_items); // Key will be 'key1'
        $stringable_key_string = (string)$stringable_key;
        self::assertTrue($fetched_items[$stringable_key_string]->isHit());
        self::assertSame('value1', $fetched_items[$stringable_key_string]->get());
    }

    #[Test]
    public function hasItemReturnsTrueForExistingItem(): void
    {
        $item = $this->sut->getItem('existing_key');
        $item->set('some_value');
        $this->sut->save($item);

        self::assertTrue($this->sut->hasItem('existing_key'));
    }

    #[Test]
    public function hasItemReturnsFalseForNonExistingItem(): void
    {
        self::assertFalse($this->sut->hasItem('non_existent_key'));
    }

    #[Test]
    public function hasItemReturnsFalseAfterItemDeleted(): void
    {
        $item = $this->sut->getItem('key_to_delete');
        $item->set('some_value');
        $this->sut->save($item);

        self::assertTrue($this->sut->hasItem('key_to_delete'));

        $this->sut->deleteItem('key_to_delete');

        self::assertFalse($this->sut->hasItem('key_to_delete'));
    }

    #[Test]
    public function clearRemovesAllItemsFromCache(): void
    {
        // Arrange: Save some items
        $item1 = $this->sut->getItem('key1');
        $item1->set('value1');
        $this->sut->save($item1);

        $item2 = $this->sut->getItem('key2');
        $item2->set('value2');
        $this->sut->saveDeferred($item2); // Add a deferred item too

        self::assertTrue($this->sut->hasItem('key1'));

        // Act: Clear the cache
        $result = $this->sut->clear();

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->sut->hasItem('key1'));
        self::assertFalse($this->sut->hasItem('key2')); // Cleared deferred items too

        // Verify with getItems
        $items = $this->sut->getItems(['key1', 'key2']);
        self::assertCount(2, $items);
        self::assertFalse($items['key1']->isHit());
        self::assertFalse($items['key2']->isHit());

        // Check internal state if possible (though not strictly necessary for black-box testing)
        // Reflection could be used here, but asserting behavior via public API is preferred.
    }

    #[Test]
    public function deleteItemRemovesItemFromCache(): void
    {
        // Arrange: Save an item
        $item = $this->sut->getItem('key_to_delete');
        $item->set('value');
        $this->sut->save($item);

        self::assertTrue($this->sut->hasItem('key_to_delete'));

        // Act: Delete the item
        $result = $this->sut->deleteItem('key_to_delete');

        // Assert
        self::assertTrue($result);
        self::assertFalse($this->sut->hasItem('key_to_delete'));

        // Verify getItem returns a miss
        $fetched_item = $this->sut->getItem('key_to_delete');
        self::assertFalse($fetched_item->isHit());
    }

    #[Test]
    public function deleteItemReturnsTrueForNonExistentItem(): void
    {
        self::assertFalse($this->sut->hasItem('non_existent_key'));
        $result = $this->sut->deleteItem('non_existent_key');
        self::assertTrue($result);
    }

    #[Test]
    public function deleteItemsRemovesMultipleItems(): void
    {
        // Arrange: Save some items
        $item1 = $this->sut->getItem('key1');
        $item1->set('v1');
        $this->sut->save($item1);

        $item2 = $this->sut->getItem('key2');
        $item2->set('v2');
        $this->sut->save($item2);

        $item3 = $this->sut->getItem('key3');
        $item3->set('v3');
        $this->sut->save($item3);

        $item4 = $this->sut->getItem('key4');
        $item4->set('v4');
        $this->sut->save($item4);

        $keys_to_delete = [
            'key1', // Existing key (string)
            new CacheKey('key3'), // Existing key (CacheKey object)
            'non_existent_key', // Non-existent key
        ];

        self::assertTrue($this->sut->hasItem('key1'));
        self::assertTrue($this->sut->hasItem('key2'));
        self::assertTrue($this->sut->hasItem('key3'));
        self::assertTrue($this->sut->hasItem('key4'));

        // Act: Delete items
        $result = $this->sut->deleteItems($keys_to_delete);

        // Assert
        self::assertTrue($result); // Should always return true

        // Verify deleted items
        self::assertFalse($this->sut->hasItem('key1'));
        self::assertFalse($this->sut->hasItem('key3'));

        // Verify remaining items
        self::assertTrue($this->sut->hasItem('key2'));
        self::assertTrue($this->sut->hasItem('key4'));
    }

    #[Test]
    public function savePersistsItemToCache(): void
    {
        // Arrange: Create a new item (not fetched from pool initially)
        // Note: Realistically, items should usually be fetched via getItem first.
        // However, the interface allows saving any CacheItemInterface.
        $item = new CacheItem(new CacheKey('new_item_key'), $this->clock);
        $item->set('new_value');

        self::assertFalse($this->sut->hasItem('new_item_key')); // Verify it's not there yet

        // Act: Save the item
        $result = $this->sut->save($item);

        // Assert
        self::assertTrue($result);
        self::assertTrue($this->sut->hasItem('new_item_key'));

        $fetched_item = $this->sut->getItem('new_item_key');
        self::assertTrue($fetched_item->isHit());
        self::assertSame('new_value', $fetched_item->get());
        self::assertSame($item->getKey(), $fetched_item->getKey()); // Check key normalization implicitly
    }

    #[Test]
    public function saveDeferredQueuesItemWithoutSavingImmediately(): void
    {
        // Arrange
        $item = $this->sut->getItem('deferred_key');
        $item->set('deferred_value');

        // Act
        $result = $this->sut->saveDeferred($item);

        // Assert
        self::assertTrue($result);

        // Verify item is not yet saved in the main cache
        self::assertFalse($this->sut->hasItem('deferred_key'));

        // Verify fetching the item again results in a miss
        $fetched_item = $this->sut->getItem('deferred_key');
        self::assertFalse($fetched_item->isHit());

        // Note: We will test the commit part in a separate test
    }

    #[Test]
    public function commitPersistsDeferredItemsAndClearsQueue(): void
    {
        // Arrange: Defer some items and save one directly
        $item1 = $this->sut->getItem('deferred_key1');
        $item1->set('value1');
        $this->sut->saveDeferred($item1);

        $item2 = $this->sut->getItem('deferred_key2');
        $item2->set('value2');
        $this->sut->saveDeferred($item2);

        $item3 = $this->sut->getItem('direct_key');
        $item3->set('value3');
        $this->sut->save($item3);

        // Verify deferred items are not yet saved
        self::assertFalse($this->sut->hasItem('deferred_key1'));
        self::assertFalse($this->sut->hasItem('deferred_key2'));
        self::assertTrue($this->sut->hasItem('direct_key'));

        // Act: Commit the deferred items
        $result = $this->sut->commit();

        // Assert: Commit successful and items are now saved
        self::assertTrue($result);
        self::assertTrue($this->sut->hasItem('deferred_key1'));
        self::assertTrue($this->sut->hasItem('deferred_key2'));
        self::assertTrue($this->sut->hasItem('direct_key')); // Direct save unaffected

        // Verify values
        $fetched1 = $this->sut->getItem('deferred_key1');
        self::assertSame('value1', $fetched1->get());
        $fetched2 = $this->sut->getItem('deferred_key2');
        self::assertSame('value2', $fetched2->get());

        // Verify queue is cleared: Defer another item and commit again
        $item4 = $this->sut->getItem('deferred_key4');
        $item4->set('value4');
        $this->sut->saveDeferred($item4);

        self::assertFalse($this->sut->hasItem('deferred_key4')); // Not saved yet

        $commit_again_result = $this->sut->commit();
        self::assertTrue($commit_again_result);
        self::assertTrue($this->sut->hasItem('deferred_key4'));
        $fetched4 = $this->sut->getItem('deferred_key4');
        self::assertSame('value4', $fetched4->get());
    }

    public static function providesNormalizedKeys(): \Generator
    {
        yield ['key', 'key'];
        yield ['key_with_underscore', 'key_with_underscore'];
        yield ['key.with.dots', 'key.with.dots'];
        yield ['key with spaces', 'key_with_spaces'];
        yield ['key:with:colons', 'key_with_colons'];
        yield ['key{with}braces', 'key_with_braces'];
        yield ['key(with)parens', 'key_with_parens'];
        yield ['key/with/slashes', 'key_with_slashes'];
        yield ['key@with@at', 'key_with_at'];
        yield ['key\\with\\backslashes', 'key_with_backslashes'];
        yield ['key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes', 'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes'];
        yield [CacheKey::class . ':1234', 'phone_burner_pinch_component_cache_cache_key_1234'];

        yield [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes';
                }
            },
            'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes',
        ];
    }
}
