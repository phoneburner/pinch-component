<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Exception;

use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicException;
use PhoneBurner\Pinch\Component\Cryptography\Exception\CryptographicLogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CryptographicLogicException::class)]
final class CryptoLogicExceptionTest extends TestCase
{
    #[Test]
    public function unreachableCreatesExceptionWithCorrectMessage(): void
    {
        $exception = CryptographicLogicException::unreachable();

        self::assertInstanceOf(CryptographicLogicException::class, $exception);
        self::assertInstanceOf(CryptographicException::class, $exception);
        self::assertInstanceOf(\LogicException::class, $exception);
        self::assertSame(
            'A code path was executed that would not normally be possible under normal operation.',
            $exception->getMessage(),
        );
    }
}
