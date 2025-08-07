<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\IntToUuid;

use Generator;
use InvalidArgumentException;
use PhoneBurner\Pinch\Component\Cryptography\IntToUuid\IntegerId;
use PhoneBurner\Pinch\Component\Cryptography\IntToUuid\IntToUuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IntegerIdTest extends TestCase
{
    #[DataProvider('providesValidValuesAndNamespaces')]
    #[Test]
    public function makeInstantiatesTheIntegerId(int $value, int $namespace): void
    {
        $integer_id = IntegerId::make($value, $namespace);
        self::assertSame($value, $integer_id->value);
        self::assertSame($namespace, $integer_id->namespace);
    }

    public static function providesValidValuesAndNamespaces(): Generator
    {
        yield [0, 0];
        yield [0, 1];
        yield [1, 0];
        yield [1, 1];
        yield [\PHP_INT_MAX, 2 ** 32 - 1];

        for ($i = 0; $i < 1000; ++$i) {
            yield [
                \random_int(IntegerId::ID_MIN, IntegerId::ID_MAX),
                \random_int(IntegerId::NAMESPACE_MIN, IntegerId::NAMESPACE_MAX),
            ];
        }
    }

    #[DataProvider('providesInvalidIdValues')]
    #[Test]
    public function minimumIdValueIsChecked(int $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$value Must Be an Integer Between 0 and 9223372036854775807, inclusive.');
        IntToUuid::encode(IntegerId::make($value, 123));
    }

    public static function providesInvalidIdValues(): Generator
    {
        yield [-1];
        yield [\PHP_INT_MIN];
    }

    #[DataProvider('providesInvalidNamespaceValues')]
    #[Test]
    public function namespaceBoundariesAreChecked(int $namespace): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$namespace Must Be an Integer Between 0 and 4294967295, inclusive.');
        IntegerId::make(1, $namespace);
    }

    public static function providesInvalidNamespaceValues(): Generator
    {
        yield [-1];
        yield [2 ** 32];
        yield [\PHP_INT_MIN];
        yield [\PHP_INT_MAX];
    }
}
