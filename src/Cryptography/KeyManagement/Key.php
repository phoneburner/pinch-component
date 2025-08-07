<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\KeyManagement;

use PhoneBurner\Pinch\Exception\SerializationProhibited;
use PhoneBurner\Pinch\String\BinaryString\ImportableBinaryString;

/**
 * Represents some kind of cryptographic key in the raw binary form.
 */
interface Key extends ImportableBinaryString
{
    /**
     * @return non-empty-string The raw binary "key material" string
     */
    public function bytes(): string;

    /**
     * @return non-negative-int The length of the binary string in bytes
     */
    public function length(): int;

    /**
     * @throws SerializationProhibited Keys must declare explicit stringification
     * behavior. For most keys, where the key material is sensitive, this method should
     * throw a SerializationProhibited exception in order to not inadvertently
     * leak credentials by accidentally casting a key to a string.
     */
    public function __toString(): string;

    /**
     * @throws SerializationProhibited Keys must declare explicit serialization
     * behavior. For most non-public keys, where the key material is sensitive, this method
     * should throw a SerializationProhibited exception in order to not inadvertently
     * leak credentials by accidentally casting a key to a string.
     */
    public function __serialize(): array;

    /**
     * @param array<array-key, mixed> $data
     * @throws SerializationProhibited Keys must declare explicit deserialization
     * behavior. For most non-public keys, where the key material is sensitive, this method
     * should throw a SerializationProhibited exception.
     */
    public function __unserialize(array $data): void;

    /**
     * @throws SerializationProhibited Keys must declare explicit JSON serialization
     * behavior. For most non-public keys, where the key material is sensitive,
     * this should throw a SerializationProhibited exception.
     */
    public function jsonSerialize(): string;
}
