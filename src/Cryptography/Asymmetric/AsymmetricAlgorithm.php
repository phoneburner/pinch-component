<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519Aegis256;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519Aes256Gcm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Blake2b;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519XChaCha20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Algorithm\X25519XSalsa20Poly1305;
use PhoneBurner\Pinch\Component\Cryptography\Attribute\AlgorithmImplementation;

use function PhoneBurner\Pinch\Enum\case_attr_first;

enum AsymmetricAlgorithm
{
    /**
     * Diffie-Hellman key exchange over Curve25519 + AEGIS-256 AEAD
     *
     * This should be the default, as long as the AEGIS-256 implementation is
     * supported by the underlying libsodium library and the receiving party.
     */
    #[AlgorithmImplementation(new X25519Aegis256())]
    case X25519Aegis256;

    /**
     * Diffie-Hellman key exchange over Curve25519 + XChaCha20 + Blake2b AEAD
     *
     * The next-best choice if AEGIS-256 is unavailable
     */
    #[AlgorithmImplementation(new X25519XChaCha20Blake2b())]
    case X25519XChaCha20Blake2b;

    /**
     * Diffie-Hellman key exchange over Curve25519 + XChaCha20 + Poly130 (IETF) AEAD
     */
    #[AlgorithmImplementation(new X25519XChaCha20Poly1305())]
    case X25519XChaCha20Poly1305;

    /**
     * Diffie-Hellman key exchange over Curve25519 + AES-256-GCM AEAD
     */
    #[AlgorithmImplementation(new X25519Aes256Gcm())]
    case X25519Aes256Gcm;

    /**
     * Diffie-Hellman key exchange over Curve25519 + XSalsa20 + Poly1305
     *
     * This is a non-AEAD construction, and is the algorithm used by the
     * sodium_crypto_box_* functions. This will have the widest compatibility
     * cross-platform, but is not as secure as the AEAD constructions.
     */
    #[AlgorithmImplementation(new X25519XSalsa20Poly1305())]
    case X25519XSalsa20Poly1305;

    public function implementation(): AsymmetricEncryptionAlgorithm
    {
        $implementation = case_attr_first($this, AlgorithmImplementation::class)->algorithm ?? null;
        \assert($implementation instanceof AsymmetricEncryptionAlgorithm);
        return $implementation;
    }
}
