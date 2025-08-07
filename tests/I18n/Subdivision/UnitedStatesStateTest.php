<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\I18n\Subdivision;

use PhoneBurner\Pinch\Component\I18n\IsoLocale;
use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionCode;
use PhoneBurner\Pinch\Component\I18n\Subdivision\SubdivisionName;
use PhoneBurner\Pinch\Component\I18n\Subdivision\UnitedStatesState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnitedStatesState::class)]
final class UnitedStatesStateTest extends TestCase
{
    #[Test]
    public function enumCasesHaveCorrectValue(): void
    {
        self::assertSame('CA', UnitedStatesState::CA->value);
        self::assertSame('TX', UnitedStatesState::TX->value);
    }

    #[Test]
    public function labelReturnsCorrectSubdivisionName(): void
    {
        $state = UnitedStatesState::NY;
        $label = $state->label();

        self::assertInstanceOf(SubdivisionName::class, $label);
        self::assertSame('New York', $label->value);
        self::assertSame(IsoLocale::EN_US, $label->locale); // Assuming default locale from attribute definition
    }

    #[Test]
    public function codeReturnsCorrectSubdivisionCode(): void
    {
        $state = UnitedStatesState::FL;
        $code = $state->code();

        self::assertInstanceOf(SubdivisionCode::class, $code);
        self::assertSame(SubdivisionCode::US_FL, $code->value);
    }

    #[Test]
    public function getRegionReturnsUsa(): void
    {
        self::assertSame(Region::US, UnitedStatesState::WA->getRegion());
    }

    #[Test]
    public function allCasesHaveLabelAndCode(): void
    {
        foreach (UnitedStatesState::cases() as $case) {
            self::assertInstanceOf(SubdivisionName::class, $case->label());
            self::assertInstanceOf(SubdivisionCode::class, $case->code());
            self::assertSame(Region::US, $case->getRegion());
        }
    }

    #[Test]
    #[DataProvider('validParsableValuesDataProvider')]
    public function instanceReturnsExpectedValue(mixed $value, UnitedStatesState $state): void
    {
        self::assertSame($state, UnitedStatesState::instance($value));
    }

    #[Test]
    #[DataProvider('invalidParsableValuesDataProvider')]
    public function instanceThrowsExpectedExceptionForInvalidInput(mixed $value): void
    {
        $this->expectException(\UnexpectedValueException::class);
        UnitedStatesState::instance($value);
    }

    #[Test]
    #[DataProvider('validParsableValuesDataProvider')]
    public function parseReturnsExpectedValueForValidInput(mixed $value, UnitedStatesState $state): void
    {
        self::assertSame($state, UnitedStatesState::parse($value));
    }

    #[Test]
    #[DataProvider('invalidParsableValuesDataProvider')]
    public function parseReturnsExpectedValueForInvalidInput(mixed $value): void
    {
        self::assertNull(UnitedStatesState::parse($value));
    }

    public static function validParsableValuesDataProvider(): \Generator
    {
        foreach (UnitedStatesState::cases() as $state) {
            yield [$state, $state];
            yield [$state->value, $state];
            yield [$state->code()->value, $state];
            yield [\strtolower($state->code()->value), $state];
            yield [$state->label()->value, $state];
            yield [\strtoupper($state->label()->value), $state];
            yield [\strtolower($state->label()->value), $state];
        }

        yield from [
            ['washington', UnitedStatesState::WA],
            ['Washington DC', UnitedStatesState::DC],
            ['Washington, DC', UnitedStatesState::DC],
            ['washington d.c.', UnitedStatesState::DC],
            ['Washington, D.C.', UnitedStatesState::DC],
        ];
    }

    public static function invalidParsableValuesDataProvider(): \Iterator
    {
        yield [null];
        yield [''];
        yield ['Invalid State'];
        yield ['XX'];
        yield [123];
        yield [new \stdClass()];
        yield [SubdivisionCode::CA_ON];
        yield ["British Columbia"];
        yield ['Newfoundland'];
    }
}
