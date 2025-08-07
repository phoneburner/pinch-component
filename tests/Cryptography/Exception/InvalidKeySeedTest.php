<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidKeySeed;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidKeySeedTest extends TestCase
{
    #[Test]
    public function happyPathTestLength(): void
    {
        self::assertSame('Key Seed Must Be Exactly 16 Bytes', InvalidKeySeed::length(16)->getMessage());
    }
}
