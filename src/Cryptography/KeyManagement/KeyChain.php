<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\KeyManagement;

use PhoneBurner\Pinch\Collections\Map\GenericMapCollection;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Hash\Hash;
use PhoneBurner\Pinch\Component\Cryptography\Hash\HashAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * Keeps track of keys derived from the app key, for use in encryption and decryption.
 * As a best practice, we want to use a unique key for each type of cryptographic
 * operation, so that a compromise of one key does not compromise all of them.
 *
 * While we need to manage many shared keys, we want to limit the number of asymmetric
 * key pairs used, since the public key is shared with other parties. We derive
 * one X25519 key pair for encryption and one Ed25519 key pair for signatures
 * using a HKDF-Blake2b derivation of the 256-bit app key as the seed.
 *
 * Note: All derived keys can be cleared by calling the clear() method.
 *
 * @extends GenericMapCollection<SharedKey>
 */
final class KeyChain extends GenericMapCollection
{
    public function __construct(
        public readonly SharedKey $app_key,
        private EncryptionKeyPair|null $encryption_key_pair = null,
        private SignatureKeyPair|null $signature_key_pair = null,
    ) {
        parent::__construct();
    }

    public function shared(string|null $context = null): SharedKey
    {
        return $context === null
            ? $this->app_key
            : $this->remember($context, fn(): SharedKey => KeyDerivation::shared($this->app_key, $context));
    }

    public function encryption(): EncryptionKeyPair
    {
        return $this->encryption_key_pair ??= KeyDerivation::encryption($this->app_key);
    }

    public function signature(): SignatureKeyPair
    {
        return $this->signature_key_pair ??= KeyDerivation::signature($this->app_key);
    }

    /**
     * Lookup a signature public key by its SHA-256 key ID.
     *
     * The key ID is computed as the SHA-256 hash of the public key's raw bytes.
     * This method performs a timing-safe comparison to prevent side-channel attacks.
     *
     * @param string $key_id SHA-256 hash of the public key as a hex string (64 characters)
     * @return SignaturePublicKey|null The matching public key if found, null otherwise
     */
    public function lookup(string $key_id): SignaturePublicKey|null
    {
        $signature_public_key = $this->signature()->public();
        $public_key_hash = Hash::string($signature_public_key->bytes(), HashAlgorithm::SHA256);

        // Use timing-safe comparison to prevent side-channel attacks
        if (\hash_equals($key_id, $public_key_hash->digest(Encoding::Hex))) {
            return $signature_public_key;
        }

        return null;
    }

    #[\Override]
    public function clear(): void
    {
        $this->encryption_key_pair = null;
        $this->signature_key_pair = null;
        parent::clear();
    }
}
