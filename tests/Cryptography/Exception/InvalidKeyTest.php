<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidKeyTest extends TestCase
{
    #[Test]
    public function happyPathTestLength(): void
    {
        self::assertSame('Key Must Be Exactly 16 Bytes', InvalidKey::length(16)->getMessage());
    }

    #[Test]
    public function happyPathTestDecoding(): void
    {
        self::assertSame('Unable to Decode Key into Binary String of Expected Length', InvalidKey::decoding()->getMessage());
    }
}
