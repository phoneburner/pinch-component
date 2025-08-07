<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Hash;

use PhoneBurner\Pinch\Component\Cryptography\Attribute\HashAlgorithmMetadata;

use function PhoneBurner\Pinch\Enum\case_attr_fetch;

enum HashAlgorithm: string
{
    /**
     * BLAKE2b Cryptographic Hash and HMAC Algorithm
     *
     * Unlike the other algorithms here, which are available via the \hash_*
     * functions, we have to use the libsodium \sodium_crypto_generichash() functio
     *
     * @link https://www.rfc-editor.org/rfc/rfc7693.txt
     */
    #[HashAlgorithmMetadata(digest_bytes: 32, cryptographic: true)]
    case BLAKE2B = 'blake2b';

    /**
     * xxHash Fast Hashing Function Family (Non-Cryptographic)
     *
     * @link https://xxhash.com/
     * @link https://php.watch/versions/8.1/xxHash
     */
    #[HashAlgorithmMetadata(digest_bytes: 8, cryptographic: false)]
    case XXH3 = 'xxh3';

    #[HashAlgorithmMetadata(digest_bytes: 4, cryptographic: false)]
    case XXH32 = 'xxh32';

    #[HashAlgorithmMetadata(digest_bytes: 16, cryptographic: false)]
    case XXH128 = 'xxh128';

    /**
     * MurmurHash3 Hashing Function Family (Non-Cryptographic)
     *
     * @link https://en.wikipedia.org/wiki/MurmurHash
     * @link https://php.watch/versions/8.1/MurmurHash3
     */
    #[HashAlgorithmMetadata(digest_bytes: 4, cryptographic: false)]
    case MURMUR3A = 'murmur3a';

    #[HashAlgorithmMetadata(digest_bytes: 16, cryptographic: false)]
    case MURMUR3F = 'murmur3f';

    /**
     * SHA-3 Cryptographic Hash Function Family
     *
     * @link https://en.wikipedia.org/wiki/SHA-3
     */
    #[HashAlgorithmMetadata(digest_bytes: 28, cryptographic: true)]
    case SHA3_224 = 'sha3-224';

    #[HashAlgorithmMetadata(digest_bytes: 32, cryptographic: true)]
    case SHA3_256 = 'sha3-256';

    #[HashAlgorithmMetadata(digest_bytes: 48, cryptographic: true)]
    case SHA3_384 = 'sha3-384';

    #[HashAlgorithmMetadata(digest_bytes: 64, cryptographic: true)]
    case SHA3_512 = 'sha3-512';

    /**
     * SHA-2 Cryptographic Hash Function Family
     *
     * Note that PHP implements the FIPS version of SHA2-512/256, which has a
     * different set of initialization vectors from version used by libsodium and
     * elsewhere, which just returns the first 256-bits from the SHA-512 hash digest.
     *
     * @link https://en.wikipedia.org/wiki/SHA-2
     */
    #[HashAlgorithmMetadata(digest_bytes: 28, cryptographic: true)]
    case SHA224 = 'sha224';

    #[HashAlgorithmMetadata(digest_bytes: 32, cryptographic: true)]
    case SHA256 = 'sha256';

    #[HashAlgorithmMetadata(digest_bytes: 48, cryptographic: true)]
    case SHA384 = 'sha384';

    #[HashAlgorithmMetadata(digest_bytes: 64, cryptographic: true)]
    case SHA512 = 'sha512';

    #[HashAlgorithmMetadata(digest_bytes: 28, cryptographic: true)]
    case SHA512_224 = 'sha512/224';

    #[HashAlgorithmMetadata(digest_bytes: 32, cryptographic: true)]
    case SHA512_256_FIPS = 'sha512/256';

    /**
     * Cyclic Redundancy Check Family (Non-Cryptographic)
     *
     * @link https://en.wikipedia.org/wiki/Cyclic_redundancy_check
     */
    #[HashAlgorithmMetadata(digest_bytes: 4, cryptographic: false)]
    case CRC32 = 'crc32';

    #[HashAlgorithmMetadata(digest_bytes: 4, cryptographic: false)]
    case CRC32B = 'crc32b'; // version used by PHP crc32() function

    #[HashAlgorithmMetadata(digest_bytes: 4, cryptographic: false)]
    case CRC32C = 'crc32c'; // aka "Castagnoli" version

    /**
     * Legacy "Broken" Algorithms
     */
    #[HashAlgorithmMetadata(digest_bytes: 16, cryptographic: false, broken: true)]
    case MD5 = 'md5';

    #[HashAlgorithmMetadata(digest_bytes: 20, cryptographic: false, broken: true)]
    case SHA1 = 'sha1';

    public static function default(bool $cryptographic = true): self
    {
        return $cryptographic ? self::BLAKE2B : self::XXH3;
    }

    public function bytes(): int
    {
        return $this->metadata()->digest_bytes;
    }

    public function cryptographic(): bool
    {
        return $this->metadata()->cryptographic;
    }

    private function metadata(): HashAlgorithmMetadata
    {
        static $cache = [];
        return $cache[$this->name] ??= case_attr_fetch($this, HashAlgorithmMetadata::class);
    }
}
