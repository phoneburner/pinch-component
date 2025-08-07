<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cache;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Tests\Fixtures\StoplightState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase
{
    /**
     * @param list<int|string|\BackedEnum|\Stringable> $parts
     */
    #[DataProvider('providesTestCases')]
    #[Test]
    public function makeNormalizesKey(array $parts, string $expected): void
    {
        self::assertSame($expected, CacheKey::make(...$parts)->normalized);
    }

    public static function providesTestCases(): \Generator
    {
        yield [[0], '0'];
        yield [[1], '1'];
        yield [['0'], '0'];
        yield [['1'], '1'];
        yield [['user'], 'user'];
        yield [['FooBarProfile'], 'foo_bar_profile'];
        yield [['user', 1, 'FooBarProfile'], 'user.1.foo_bar_profile'];
        yield [['user', 0, 'FooBarProfile'], 'user.0.foo_bar_profile'];
        yield [['user', 1, 'FooBarProfile:42'], 'user.1.foo_bar_profile_42'];
        yield [['user.....', 1, 'FooBarProfile:42'], 'user.1.foo_bar_profile_42'];
        yield [[StoplightState::Red], 'red'];
        yield [[StoplightState::Red, StoplightState::Green], 'red.green'];

        yield [['test_value.42'], 'test_value.42'];
        yield [['user.1.foo_bar_profile'], 'user.1.foo_bar_profile'];
        yield [['user.1.FooBarProfile'], 'user.1.foo_bar_profile'];
        yield [['.user.1.FooBarProfile'], 'user.1.foo_bar_profile'];
        yield [['user.1.FooBarProfile.'], 'user.1.foo_bar_profile'];

        yield [['key'], 'key'];
        yield [['key_with_underscore'], 'key_with_underscore'];
        yield [['key.with.dots'], 'key.with.dots'];
        yield [['key with spaces'], 'key_with_spaces'];
        yield [['key:with:colons'], 'key_with_colons'];
        yield [['key{with}braces'], 'key_with_braces'];
        yield [['key(with)parens'], 'key_with_parens'];
        yield [['key/with/slashes'], 'key_with_slashes'];
        yield [['key@with@at'], 'key_with_at'];
        yield [['key\\with\\backslashes'], 'key_with_backslashes'];
        yield [['key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes'], 'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes'];
        yield [[StoplightState::Red, CacheKey::class . ':1234'], 'red.phone_burner_pinch_component_cache_cache_key_1234'];
        yield [[CacheKey::class . ':1234'], 'phone_burner_pinch_component_cache_cache_key_1234'];

        yield [
            [new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes';
                }
            }],
            'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes',
        ];
    }

    /**
     * @param list<int|string|\BackedEnum|\Stringable> $parts
     */
    #[DataProvider('providesEmptyStringTestCases')]
    #[Test]
    public function makeThrowsExceptionWhenEmptyStringPassed(array $parts): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key part cannot be empty string');
        CacheKey::make(...$parts);
    }

    public static function providesEmptyStringTestCases(): \Generator
    {
        yield [['']];
        yield [['user', 1, '']];
        yield [['user', '', 'FooBarProfile']];
        yield [['', '1', 'FooBarProfile']];
    }
}
