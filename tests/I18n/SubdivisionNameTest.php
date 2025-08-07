<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n;

use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SubdivisionNameTest extends TestCase
{
    #[Test]
    public function regionNamesAreUniqueAndNonEmpty(): void
    {
        $names = SubdivisionName::all();
        self::assertNotEmpty($names);
        self::assertCount(\count($names), \array_flip($names));
        /** @phpstan-ignore arrayFilter.same (intentional test of "impossible" condition) */
        self::assertCount(\count($names), \array_filter($names));
        foreach (\array_keys($names) as $key) {
            self::assertTrue(\defined(SubdivisionCode::class . '::' . $key));
        }
    }

    #[Test]
    public function displayReturnsExpectedString(): void
    {
        self::assertSame('Ohio', SubdivisionName::display('US-OH'));
    }

    #[Test]
    public function displayHandlesInvalidCase(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @phpstan-ignore argument.type (Intentional Defect) */
        SubdivisionName::display('US-ZZ');
    }

    #[Test]
    public function shortReturnsExpectedString(): void
    {
        self::assertSame('OH', SubdivisionName::short('US-OH'));
    }

    #[Test]
    public function shortHandlesInvalidCase(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        /** @phpstan-ignore argument.type (Intentional Defect) */
        SubdivisionName::short('US-ZZ');
    }
}
