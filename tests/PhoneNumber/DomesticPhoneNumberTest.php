<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\PhoneNumber;

use PhoneBurner\Pinch\Component\PhoneNumber\DomesticPhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\E164;
use PhoneBurner\Pinch\Component\PhoneNumber\Exception\InvalidPhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumber;
use PhoneBurner\Pinch\Component\PhoneNumber\PhoneNumberFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DomesticPhoneNumberTest extends TestCase
{
    #[DataProvider('invalidPhoneDataProvider')]
    #[Test]
    public function constructorValidatesPhoneNumbers(string $test): void
    {
        $this->expectException(InvalidPhoneNumber::class);
        DomesticPhoneNumber::make($test);
    }

    #[Test]
    public function makeValidatesPhoneNumbers(): void
    {
        $this->expectException(\TypeError::class);
        /* @phpstan-ignore-next-line intentional defect */
        DomesticPhoneNumber::make(new \stdClass());
    }

    #[DataProvider('invalidPhoneDataProvider')]
    #[Test]
    public function tryFromReturnsNullOnInvalidValidatesPhoneNumbers(string $test): void
    {
        self::assertNull(DomesticPhoneNumber::tryFrom($test));
    }

    #[DataProvider('e164StringDataProvider')]
    #[Test]
    public function tryFromReturnsSelf(string $test): void
    {
        $phone_number = DomesticPhoneNumber::make($test);
        self::assertSame($phone_number, DomesticPhoneNumber::tryFrom($phone_number));
    }

    #[DataProvider('e164StringDataProvider')]
    #[Test]
    public function tryFromMakesValidPhoneNumbers(string $test): void
    {
        self::assertEquals(DomesticPhoneNumber::make($test), DomesticPhoneNumber::tryFrom($test));
    }

    #[DataProvider('e164StringDataProvider')]
    #[Test]
    public function makeReturnsSelf(string $test): void
    {
        $phone_number = DomesticPhoneNumber::make($test);
        self::assertSame($phone_number, DomesticPhoneNumber::make($phone_number));
    }

    #[DataProvider('e164StringDataProvider')]
    #[Test]
    public function makeCastsStringable(string $test): void
    {
        $phone_number = DomesticPhoneNumber::make($test);
        self::assertEquals($phone_number, DomesticPhoneNumber::make(new readonly class ($test) implements \Stringable {
            public function __construct(private string $phone_number)
            {
            }

            public function __toString(): string
            {
                return $this->phone_number;
            }
        }));
    }

    #[DataProvider('e164StringDataProvider')]
    #[Test]
    public function makeReusesValidE164(string $test): void
    {
        $e164 = E164::make($test);
        self::assertSame($e164, DomesticPhoneNumber::make(new readonly class ($e164) implements PhoneNumber {
            public function __construct(private E164 $e164)
            {
            }

            public function toE164(): E164
            {
                return $this->e164;
            }
        })->toE164());
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function jsonSerializeReturnsExpectedString(string $test, string $expected): void
    {
        self::assertSame($expected, DomesticPhoneNumber::make($test)->jsonSerialize());
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function itCanBeSerializedAndDeserialized(string $test, string $expected): void
    {
        $phone_number = DomesticPhoneNumber::make($test);

        $serialized = \serialize($phone_number);
        $deserialized = \unserialize($serialized, ['allowed_classes' => [DomesticPhoneNumber::class]]);

        self::assertInstanceOf(DomesticPhoneNumber::class, $deserialized);
        self::assertEquals($phone_number, $deserialized);
        self::assertSame($expected, (string)$deserialized->toE164());
    }

    #[DataProvider('formatDataProvider')]
    #[Test]
    public function formatReturnsThePhoneNumberExpectedStringFormat(string $test, array $formats): void
    {
        self::assertSame(
            $formats['national'],
            DomesticPhoneNumber::make($test)->format(),
        );

        self::assertSame(
            $formats['national'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::National),
        );

        self::assertSame(
            $formats['strip_prefix'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::StripPrefix),
        );

        self::assertSame(
            $formats['e164'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::E164),
        );

        self::assertSame(
            $formats['international'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::International),
        );

        self::assertSame(
            $formats['rfc3966'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::Rfc3966),
        );
    }

    #[Test]
    public function npaNxxAndLineFunctionsReturnExpected(): void
    {
        $phone = DomesticPhoneNumber::make('3145550123');

        self::assertSame('314', $phone->npa());
        self::assertSame('555', $phone->nxx());
        self::assertSame('0123', $phone->line());
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function toStringReturnsTheUnformattedPhoneNumber(string $test, string $normalized): void
    {
        $phone = DomesticPhoneNumber::make($test);
        self::assertSame((string)E164::make($normalized), (string)$phone);
    }

    #[DataProvider('provideAreaCodes')]
    #[Test]
    public function getAreaCodeParsesAppropriateString(string $test, int $expected): void
    {
        self::assertSame($expected, DomesticPhoneNumber::make($test)->getAreaCode()->npa);
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function toE164ReturnsExpectedPhoneNumber(string $test, string $expected): void
    {
        $phone = DomesticPhoneNumber::make($test);

        self::assertSame($expected, $phone->toE164()->value);
    }

    /**
     * @return \Generator<array{string, int}>
     */
    public static function provideAreaCodes(): \Generator
    {
        yield ['3145551234', 314];
        yield ['13145551234', 314];
        yield ['1-314-555-1234', 314];
        yield ["3145551234\n", 314];
        yield ["13145551234\n", 314];
        yield ["(314) 555-1234", 314];
        yield ["(314)-555-1234", 314];
        yield ["(314)-555-1234 ext.", 314];
    }

    /**
     * @return \Generator<array{string}>
     */
    public static function e164StringDataProvider(): \Generator
    {
        yield from [
            ['+13145551234'],
            ['3145551234'],
            ['13145551234'],
            ['1-314-555-1234'],
            ["3145551234\n"],
            ["13145551234\n"],
            ["(314) 555-1234"],
            ["(314)-555-1234"],
            ["(314)-555-1234 ext."],
        ];
    }

    /**
     * @return \Generator<array{string, string}>
     */
    public static function e164DataProvider(): \Generator
    {
        yield from [
            ['3145551234', '+13145551234'],
            ['13145551234', '+13145551234'],
            ['1-314-555-1234', '+13145551234'],
            ["3145551234\n", '+13145551234'],
            ["13145551234\n", '+13145551234'],
            ["(314) 555-1234", '+13145551234'],
            ["(314)-555-1234", '+13145551234'],
            ["(314)-555-1234 ext.", '+13145551234'],
        ];
    }

    /**
     * @return \Generator<array{string, array<string,string>}>
     */
    public static function formatDataProvider(): \Generator
    {
        $formatted = [
            'national' => '(314) 555-1234',
            'strip_prefix' => '3145551234',
            'e164' => '+13145551234',
            'international' => '+1 314-555-1234',
            'rfc3966' => 'tel:+1-314-555-1234',
        ];

        yield from [
            ['3145551234', $formatted],
            ['13145551234', $formatted],
            ['1-314-555-1234 ', $formatted],
            ["3145551234\n", $formatted],
            ["13145551234\n", $formatted],
            ["(314) 555-1234 ", $formatted],
            ["(314)-555-1234", $formatted],
            ["(314)-555-1234 ext.", $formatted],
        ];
    }

    /**
     * @return \Generator<array{string}>
     */
    public static function invalidPhoneDataProvider(): \Generator
    {
        yield from [
            ['invalid'],
            ['0'],
            ['314AAA1234'],
            ['013145551234'],
            ['131455512345'],
            ['1234567890'],
            ['1234567890'],
            ['3140551234'],
            ['3140551234'],
        ];
    }
}
