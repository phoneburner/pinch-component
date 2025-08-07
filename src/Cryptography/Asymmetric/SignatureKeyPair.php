<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Exception\InvalidKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyId;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringExportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringProhibitsSerialization;

use function PhoneBurner\Pinch\String\bytes;

/**
 * Holds a secret key and the corresponding public key for signing and verifying
 * messages using ED25519 (EdDSA).
 */
final readonly class SignatureKeyPair implements KeyPair
{
    use BinaryStringProhibitsSerialization;
    use BinaryStringExportBehavior;
    use BinaryStringImportBehavior;

    public const int LENGTH = \SODIUM_CRYPTO_SIGN_KEYPAIRBYTES;

    public SignatureSecretKey $secret;

    public SignaturePublicKey $public;

    public function __construct(#[\SensitiveParameter] BinaryString|string $bytes)
    {
        $bytes = bytes($bytes);
        if (\strlen($bytes) !== self::LENGTH) {
            throw InvalidKeyPair::length(self::LENGTH);
        }

        $this->secret = new SignatureSecretKey(\sodium_crypto_sign_secretkey($bytes));
        $this->public = new SignaturePublicKey(\sodium_crypto_sign_publickey($bytes));
    }

    public static function generate(): static
    {
        return new self(\sodium_crypto_sign_keypair());
    }

    /**
     * Important: Unlike the EncryptionKeyPair, the seed for the SignatureKeyPair
     * is used as the first 256-bits of the 512-bit secret key. Therefore, using a
     * key derivation function to create the seed from a primary key would be a
     * *really* good idea.
     */
    public static function fromSeed(#[\SensitiveParameter] SignatureKeyPairSeed $seed): static
    {
        return new self(\sodium_crypto_sign_seed_keypair($seed->bytes()));
    }

    public static function fromSecretKey(#[\SensitiveParameter] SignatureSecretKey $secret_key): self
    {
        return new self(\sodium_crypto_sign_keypair_from_secretkey_and_publickey(
            $secret_key->bytes(),
            \sodium_crypto_sign_publickey_from_secretkey($secret_key->bytes()),
        ));
    }

    public function id(): KeyId
    {
        return new KeyId($this->public);
    }

    public function secret(): SignatureSecretKey
    {
        return $this->secret;
    }

    public function public(): SignaturePublicKey
    {
        return $this->public;
    }

    /**
     * @return non-empty-string
     */
    public function bytes(): string
    {
        return $this->secret->bytes() . $this->public->bytes();
    }

    public function length(): int
    {
        return self::LENGTH;
    }
}
