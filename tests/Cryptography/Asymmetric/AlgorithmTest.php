<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519Aes256Gcm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Blake2b;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519XSalsa20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AlgorithmTest extends TestCase
{
    #[Test]
    public function happyPathForImplementation(): void
    {
        self::assertInstanceOf(X25519XChaCha20Blake2b::class, AsymmetricAlgorithm::X25519XChaCha20Blake2b->implementation());
        self::assertInstanceOf(X25519XChaCha20Poly1305::class, AsymmetricAlgorithm::X25519XChaCha20Poly1305->implementation());
        self::assertInstanceOf(X25519XSalsa20Poly1305::class, AsymmetricAlgorithm::X25519XSalsa20Poly1305->implementation());
        self::assertInstanceOf(X25519Aes256Gcm::class, AsymmetricAlgorithm::X25519Aes256Gcm->implementation());
    }
}
