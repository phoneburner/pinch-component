<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache\Psr6;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Cache\Psr6\NullCacheItem;
use PhoneBurner\Pinch\Component\Cache\Psr6\NullCachePool;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullCachePoolTest extends TestCase
{
    private NullCachePool $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new NullCachePool();
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function getItemAlwaysReturnsNullCacheItemMiss(
        string|\Stringable $raw_key,
        string $normalized_key,
    ): void {
        // Try saving first (should have no effect)
        $item_to_save = $this->sut->getItem($raw_key);
        $item_to_save->set('test_value');
        $this->sut->save($item_to_save); // @phpstan-ignore-line

        // Fetch the item
        $fetched_item = $this->sut->getItem($raw_key);

        self::assertInstanceOf(NullCacheItem::class, $fetched_item);
        self::assertFalse($fetched_item->isHit());
        self::assertNull($fetched_item->get());
        self::assertSame($normalized_key, $fetched_item->getKey());
    }

    #[Test]
    public function getItemReturnsMissWhenItemNotFound(): void // Kept for clarity, same logic as above
    {
        $item = $this->sut->getItem('non_existent_key');

        self::assertInstanceOf(NullCacheItem::class, $item);
        self::assertFalse($item->isHit());
        self::assertSame('non_existent_key', $item->getKey());
        self::assertNull($item->get());
    }

    #[Test]
    public function getItemsAlwaysReturnsNullCacheItemMisses(): void
    {
        // Arrange: Prepare keys, including duplicates and non-existent
        $key3_obj = new CacheKey('key3'); // Key object
        $stringable_key = new class implements \Stringable {
            public function __toString(): string
            {
                return 'key1'; // Duplicate of 'key1' string
            }
        };

        $keys_to_fetch = [
            'key1' => 'key1',
            'key2_cachekey' => new CacheKey('key2'),
            'non_existent' => 'non_existent',
            'key1_stringable' => $stringable_key,
            'key3_obj_key' => $key3_obj,
        ];

        // Attempt saves (should have no effect)
        $item1 = $this->sut->getItem('key1');
        $item1->set('value1');
        $this->sut->save($item1); // @phpstan-ignore-line
        $item2 = $this->sut->getItem('key2');
        $item2->set('value2');
        $this->sut->save($item2); // @phpstan-ignore-line
        $item3 = $this->sut->getItem($key3_obj);
        $item3->set('value3');
        $this->sut->save($item3); // @phpstan-ignore-line

        // Act: Fetch items
        $fetched_items = $this->sut->getItems(\array_values($keys_to_fetch));

        // Assert: Check fetched items (expect size based on unique string keys)
        // 'key1', 'key2', 'non_existent', 'key3' = 4 unique keys
        self::assertCount(4, $fetched_items);

        // Check items are NullCacheItem misses
        foreach ($fetched_items as $key => $item) {
            self::assertInstanceOf(NullCacheItem::class, $item);
            self::assertFalse($item->isHit(), \sprintf("Item with key '%s' should be a miss", $key));
            self::assertNull($item->get(), \sprintf("Item with key '%s' should have null value", $key));
        }

        // Check specific keys exist in the output (keyed by string representation)
        self::assertArrayHasKey('key1', $fetched_items);
        self::assertArrayHasKey('key2', $fetched_items);
        self::assertArrayHasKey('non_existent', $fetched_items);
        self::assertArrayHasKey('key3', $fetched_items); // Keyed by string 'key3'
    }

    #[Test]
    public function hasItemAlwaysReturnsFalse(): void
    {
        // Arrange: Try saving an item (should have no effect)
        $item = $this->sut->getItem('existing_key');
        $item->set('some_value');
        $this->sut->save($item); // @phpstan-ignore-line

        // Assert
        self::assertFalse($this->sut->hasItem('existing_key'));
        self::assertFalse($this->sut->hasItem('non_existent_key'));
    }

    #[Test]
    public function clearAlwaysReturnsTrue(): void
    {
        // Arrange: Try saving items (should have no effect)
        $item1 = $this->sut->getItem('key1');
        $item1->set('value1');
        $this->sut->save($item1); // @phpstan-ignore-line
        $item2 = $this->sut->getItem('key2');
        $item2->set('value2');
        $this->sut->saveDeferred($item2); // @phpstan-ignore-line

        // Act: Clear the cache
        $result = $this->sut->clear();

        // Assert
        self::assertTrue($result);
        // Verify state hasn't changed (i.e., items are still misses)
        self::assertFalse($this->sut->hasItem('key1'));
        self::assertFalse($this->sut->hasItem('key2'));
    }

    #[Test]
    public function deleteItemAlwaysReturnsTrue(): void
    {
        // Arrange: Try saving an item (should have no effect)
        $item = $this->sut->getItem('key_to_delete');
        $item->set('value');
        $this->sut->save($item); // @phpstan-ignore-line

        // Act: Delete the item (existing or not)
        $result_existing = $this->sut->deleteItem('key_to_delete');
        $result_non_existing = $this->sut->deleteItem('non_existent_key');

        // Assert
        self::assertTrue($result_existing);
        self::assertTrue($result_non_existing);
        self::assertFalse($this->sut->hasItem('key_to_delete')); // Still false after delete
    }

    #[Test]
    public function deleteItemsAlwaysReturnsTrue(): void
    {
        // Arrange: Try saving items (should have no effect)
        $item1 = $this->sut->getItem('key1');
        $item1->set('v1');
        $this->sut->save($item1); // @phpstan-ignore-line
        $item2 = $this->sut->getItem('key2');
        $item2->set('v2');
        $this->sut->save($item2); // @phpstan-ignore-line

        $keys_to_delete = [
            'key1', // "Existing" key (string)
            new CacheKey('key3'), // Non-existent key (CacheKey object)
            'non_existent_key', // Non-existent key
        ];

        // Act: Delete items
        $result = $this->sut->deleteItems($keys_to_delete);

        // Assert
        self::assertTrue($result); // Should always return true

        // Verify items are still misses
        self::assertFalse($this->sut->hasItem('key1'));
        self::assertFalse($this->sut->hasItem('key2')); // Untouched item
        self::assertFalse($this->sut->hasItem('key3'));
        self::assertFalse($this->sut->hasItem('non_existent_key'));
    }

    #[Test]
    public function saveAlwaysReturnsTrueAndDoesNotPersist(): void
    {
        // Arrange: Create a new item
        // Use NullCacheItem directly or via pool->getItem, result is the same
        $item = $this->sut->getItem('new_item_key');
        $item->set('new_value');

        self::assertFalse($this->sut->hasItem('new_item_key')); // Verify it's not there yet

        // Act: Save the item
        $result = $this->sut->save($item);

        // Assert: Save returns true, but item is still a miss
        self::assertTrue($result);
        self::assertFalse($this->sut->hasItem('new_item_key'));

        $fetched_item = $this->sut->getItem('new_item_key');
        self::assertFalse($fetched_item->isHit());
        self::assertNull($fetched_item->get());
    }

    #[Test]
    public function saveDeferredAlwaysReturnsTrueAndDoesNotQueue(): void
    {
        // Arrange
        $item = $this->sut->getItem('deferred_key');
        $item->set('deferred_value');

        // Act
        $result = $this->sut->saveDeferred($item);

        // Assert: Returns true, item remains a miss
        self::assertTrue($result);
        self::assertFalse($this->sut->hasItem('deferred_key'));

        $fetched_item = $this->sut->getItem('deferred_key');
        self::assertFalse($fetched_item->isHit());

        // Verify commit doesn't save it either
        $commit_result = $this->sut->commit();
        self::assertTrue($commit_result);
        self::assertFalse($this->sut->hasItem('deferred_key'));
    }

    #[Test]
    public function commitAlwaysReturnsTrueAndHasNoEffect(): void
    {
        // Arrange: Defer items (should have no effect)
        $item1 = $this->sut->getItem('deferred_key1');
        $item1->set('value1');
        $this->sut->saveDeferred($item1); // @phpstan-ignore-line
        $item2 = $this->sut->getItem('deferred_key2');
        $item2->set('value2');
        $this->sut->saveDeferred($item2); // @phpstan-ignore-line

        // Verify deferred items are not saved
        self::assertFalse($this->sut->hasItem('deferred_key1'));
        self::assertFalse($this->sut->hasItem('deferred_key2'));

        // Act: Commit the deferred items
        $result = $this->sut->commit();

        // Assert: Commit successful, items are still misses
        self::assertTrue($result);
        self::assertFalse($this->sut->hasItem('deferred_key1'));
        self::assertFalse($this->sut->hasItem('deferred_key2'));
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
