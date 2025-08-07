<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Symmetric;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidStringLength;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Exception\SerializationProhibited;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class SharedKeyTest extends TestCase
{
    #[Test]
    public function happyPath(): void
    {
        $key = SharedKey::generate();
        self::assertSame(SharedKey::LENGTH, $key->length());

        $bytes = \random_bytes(SharedKey::LENGTH);
        $key = new SharedKey($bytes);
        self::assertSame($bytes, $key->bytes());

        $encoded = \base64_encode($bytes);
        $key = SharedKey::import($encoded);
        self::assertEquals($key, SharedKey::import($encoded));
    }

    #[Test]
    #[TestWith([SharedKey::LENGTH - 1])]
    #[TestWith([SharedKey::LENGTH + 1])]
    #[TestWith([0])]
    public function keyRequiresExactLength(int $length): void
    {
        $key_material = $length > 0 ? \random_bytes($length) : '';

        $this->expectException(InvalidStringLength::class);
        new SharedKey($key_material);
    }

    #[Test]
    public function keyCannotBeSerialized(): void
    {
        $key = SharedKey::generate();

        $this->expectException(SerializationProhibited::class);
        \serialize($key);
    }
}
