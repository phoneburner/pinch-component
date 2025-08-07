<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Symmetric;

use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\Aes256Gcm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\XChaCha20Blake2b;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\XChaCha20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Algorithm\XSalsa20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AlgorithmTest extends TestCase
{
    #[Test]
    public function happyPathForImplementation(): void
    {
        self::assertInstanceOf(XChaCha20Blake2b::class, SymmetricAlgorithm::XChaCha20Blake2b->implementation());
        self::assertInstanceOf(XChaCha20Poly1305::class, SymmetricAlgorithm::XChaCha20Poly1305->implementation());
        self::assertInstanceOf(XSalsa20Poly1305::class, SymmetricAlgorithm::XSalsa20Poly1305->implementation());
        self::assertInstanceOf(Aes256Gcm::class, SymmetricAlgorithm::Aes256Gcm->implementation());
    }
}
