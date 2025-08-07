<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidSignature;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidSignatureTest extends TestCase
{
    #[Test]
    public function happyPathTestLength(): void
    {
        self::assertSame("Message Signature Must Be Exactly 32 Bytes", InvalidSignature::length(32)->getMessage());
    }
}
