<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Attribute;

use PhoneBurner\Pinch\Component\Cryptography\Attribute\HashAlgorithmMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(HashAlgorithmMetadata::class)]
final class HashAlgorithmMetadataTest extends TestCase
{
    #[Test]
    public function constructsWithOnlyRequiredArguments(): void
    {
        $digest_bytes = 32;
        $properties = new HashAlgorithmMetadata($digest_bytes);

        self::assertInstanceOf(HashAlgorithmMetadata::class, $properties);
        self::assertSame($digest_bytes, $properties->digest_bytes);
        self::assertFalse($properties->cryptographic);
        self::assertFalse($properties->broken);
    }

    #[Test]
    public function constructsWithAllArguments(): void
    {
        $digest_bytes = 64;
        $cryptographic = true;
        $broken = true;

        $properties = new HashAlgorithmMetadata(
            digest_bytes: $digest_bytes,
            cryptographic: $cryptographic,
            broken: $broken,
        );

        self::assertInstanceOf(HashAlgorithmMetadata::class, $properties);
        self::assertSame($digest_bytes, $properties->digest_bytes);
        self::assertTrue($properties->cryptographic);
        self::assertTrue($properties->broken);
    }
}
