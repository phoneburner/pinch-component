<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionPublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\EncryptedMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Message\MultipleRecipientMessageBox;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Hash\Hash;
use PhoneBurner\Pinch\Component\Cryptography\Hash\HashAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Hash\Hmac;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyChain;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\DecodedPasetoMessage;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoFooterClaims;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoPayloadClaims;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoWithClaims;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol\PasetoFacade;
use PhoneBurner\Pinch\Component\Cryptography\String\Ciphertext;
use PhoneBurner\Pinch\Component\Cryptography\String\MessageSignature;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\EncryptedMessage;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\Symmetric;
use PhoneBurner\Pinch\Random\Randomizer;
use PhoneBurner\Pinch\String\BinaryString\BinaryString;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PhoneBurner\Pinch\Time\Clock\SystemClock;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;

use function PhoneBurner\Pinch\String\bytes;

use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

/**
 * A facade pattern implementation around our cryptographic utilities, which are
 * almost entirely based on the Sodium extension. Using the facade instead of the
 * concrete implementations allows us a bit more future flexibility in changing
 * the underlying implementation (or adding configuration), should that ever be
 * necessary.
 *
 * Without the facade, consuming code would need to bring in several related classes
 * that interact. This way, they only need to know about the Natrium class, and can
 * use it to access the various cryptographic functions.
 *
 * Note: we don't directly expose the asymmetric seal/unseal methods, as we should
 * always prefer authenticated encryption to anonymous encryption, unless there is
 * a specific reason to use the latter. Those methods can be accessed through the
 * public $asymmetric property, if needed.
 */
#[Contract]
readonly class Natrium
{
    public Symmetric $symmetric;
    public Asymmetric $asymmetric;
    public PasetoFacade $paseto;
    public Randomizer $random;

    public function __construct(
        public KeyChain $keys,
        public Clock $clock = new SystemClock(),
        public Defaults $defaults = new Defaults(),
    ) {
        $this->symmetric = new Symmetric();
        $this->asymmetric = new Asymmetric();
        $this->paseto = new PasetoFacade();
        $this->random = new Randomizer();
    }

    public function hash(\Stringable|BinaryString|string $plaintext): Hash
    {
        return Hash::string(bytes($plaintext), HashAlgorithm::BLAKE2B);
    }

    public function hmac(\Stringable|BinaryString|string $plaintext, string|null $context = null): Hmac
    {
        return Hmac::string(bytes($plaintext), $this->keys->shared($context), HashAlgorithm::BLAKE2B);
    }

    public function encrypt(
        \Stringable|BinaryString|string $plaintext,
        string|null $context = null,
        \Stringable|BinaryString|string $additional_data = '',
    ): EncryptedMessage {
        return $this->symmetric->encrypt(
            $this->keys->shared($context),
            bytes($plaintext),
            bytes($additional_data),
            $this->defaults->symmetric,
        );
    }

    public function decrypt(
        EncryptedMessage|Ciphertext $ciphertext,
        string|null $context = null,
        \Stringable|BinaryString|string $additional_data = '',
    ): string|null {
        return $this->symmetric->decrypt(
            $this->keys->shared($context),
            $ciphertext,
            bytes($additional_data),
            $this->defaults->symmetric,
        );
    }

    public function sign(
        \Stringable|BinaryString|string $plaintext,
        string|null $context = null,
    ): MessageSignature {
        return $this->symmetric->sign(
            $this->keys->shared($context),
            bytes($plaintext),
        );
    }

    public function verify(
        \Stringable|BinaryString|string $plaintext,
        MessageSignature $signature,
        string|null $context = null,
    ): bool {
        return $this->symmetric->verify(
            $this->keys->shared($context),
            $signature,
            bytes($plaintext),
        );
    }

    public function encryptWithPublicKey(
        EncryptionPublicKey $public_key,
        \Stringable|BinaryString|string $plaintext,
        \Stringable|BinaryString|string $additional_data = '',
    ): EncryptedMessageBox {
        return $this->asymmetric->encrypt(
            $this->keys->encryption(),
            $public_key,
            bytes($plaintext),
            bytes($additional_data),
            $this->defaults->asymmetric,
        );
    }

    /**
     * Asymmetric decryption using the public key of the sender to authenticate
     * that the message was sent by them, and the secret key of the recipient to
     * decrypt the message.
     */
    public function decryptWithSecretKey(
        EncryptionPublicKey $public_key,
        EncryptedMessageBox|Ciphertext $ciphertext,
        \Stringable|BinaryString|string $additional_data = '',
    ): string|null {
        return $this->asymmetric->decrypt(
            $this->keys->encryption(),
            $public_key,
            $ciphertext,
            bytes($additional_data),
            $this->defaults->asymmetric,
        );
    }

    /**
     * Create a 512-bit Ed25519 digital signature for a message using the secret key, so that anyone
     * with the public key can verify the authenticity of the message.
     */
    public function signWithSecretKey(\Stringable|BinaryString|string $plaintext): MessageSignature
    {
        return $this->asymmetric->sign(
            $this->keys->signature(),
            bytes($plaintext),
        );
    }

    /**
     * Verify the authenticity of a plaintext message with a detached message
     * signature produced with the sender's secret key, using their known public key.
     */
    public function verifyWithPublicKey(
        SignaturePublicKey $sender_public_key,
        MessageSignature $signature,
        \Stringable|BinaryString|string $plaintext,
    ): bool {
        return $this->asymmetric->verify(
            $sender_public_key,
            $signature,
            bytes($plaintext),
        );
    }

    /**
     * @param array<string, mixed> $custom_payload_claims
     * @param array<string, mixed> $custom_footer_claims
     */
    public function encryptPaseto(
        \Stringable|string|null $subject = null,
        \Stringable|string|null $issuer = null,
        \Stringable|string|null $audience = null,
        \DateTimeImmutable|TimeInterval $expiration = new TimeInterval(seconds: 10 * SECONDS_IN_MINUTE),
        array $custom_payload_claims = [],
        array $custom_footer_claims = [],
    ): PasetoWithClaims {
        return $this->paseto->encrypt(
            $this->keys->shared(),
            new PasetoPayloadClaims(
                iss: $issuer,
                sub: $subject,
                aud: $audience,
                iat: $this->clock->now(),
                exp: $expiration,
                other: $custom_payload_claims,
            ),
            new PasetoFooterClaims(other: $custom_footer_claims),
        );
    }

    public function decryptPaseto(
        Paseto|PasetoWithClaims $token,
    ): DecodedPasetoMessage|null {
        try {
            return DecodedPasetoMessage::make($this->paseto->decrypt($this->keys->shared(), $token->token()));
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $custom_payload_claims
     * @param array<string, mixed> $custom_footer_claims
     */
    public function signPaseto(
        \Stringable|string|null $subject = null,
        \Stringable|string|null $issuer = null,
        \Stringable|string|null $audience = null,
        \DateTimeImmutable|TimeInterval $expiration = new TimeInterval(seconds: 10 * SECONDS_IN_MINUTE),
        array $custom_payload_claims = [],
        array $custom_footer_claims = [],
    ): PasetoWithClaims {
        return $this->paseto->sign(
            $this->keys->signature(),
            new PasetoPayloadClaims(
                iss: $issuer,
                sub: $subject,
                aud: $audience,
                iat: $this->clock->now(),
                exp: $expiration,
                other: $custom_payload_claims,
            ),
            new PasetoFooterClaims(other: $custom_footer_claims),
        );
    }

    public function verifyPaseto(
        Paseto|PasetoWithClaims $token,
        SignaturePublicKey|null $public_key = null,
    ): DecodedPasetoMessage|null {
        try {
            return DecodedPasetoMessage::make($this->paseto->verify($public_key ?? $this->keys->signature()->public, $token->token()));
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param list<string>|null $valid_issuers
     * @param list<string>|null $valid_subjects
     * @param list<string>|null $valid_audiences
     */
    public function validatePaseto(
        DecodedPasetoMessage|null $decoded_token,
        array|null $valid_issuers = null,
        array|null $valid_subjects = null,
        array|null $valid_audiences = null,
    ): bool {
        if ($decoded_token === null) {
            return false;
        }

        $now = $this->clock->now();

        if ($decoded_token->payload->iat instanceof \DateTimeImmutable && $decoded_token->payload->iat > $now) {
            return false;
        }

        if ($decoded_token->payload->nbf instanceof \DateTimeImmutable && $decoded_token->payload->nbf > $now) {
            return false;
        }

        if ($decoded_token->payload->exp instanceof \DateTimeImmutable && $decoded_token->payload->exp < $now) {
            return false;
        }

        if ($valid_issuers && ! \in_array($decoded_token->payload->iss, $valid_issuers, true)) {
            return false;
        }

        if ($valid_subjects && ! \in_array($decoded_token->payload->sub, $valid_subjects, true)) {
            return false;
        }
        return ! ($valid_audiences && ! \in_array($decoded_token->payload->aud, $valid_audiences, true));
    }

    /**
     * @param array<EncryptionPublicKey> $public_keys
     */
    public function encryptMessageForMultiplePublicKeys(
        array $public_keys,
        \Stringable|BinaryString|string $plaintext,
        \Stringable|BinaryString|string $additional_data = '',
    ): MultipleRecipientMessageBox {

        // Generate brand new symmetric key and nonce from random bytes
        $symmetric_key = SharedKey::generate();

        // Encrypt the plaintext message with the symmetric key
        $message = $this->symmetric->encrypt(
            $symmetric_key,
            bytes($plaintext),
            bytes($additional_data),
            $this->defaults->symmetric,
        );

        // Add our public key to the list of keys so we can decrypt the message later
        if (! \in_array($this->keys->encryption()->public, $public_keys, false)) {
            $public_keys[] = $this->keys->encryption()->public;
        }

        $encapsulated_keys = [];
        foreach ($public_keys as $public_key) {
            \assert($public_key instanceof EncryptionPublicKey);
            $encapsulated_keys[] = $this->asymmetric->encrypt($this->keys->encryption(), $public_key, $symmetric_key->bytes());
        }

        return new MultipleRecipientMessageBox(
            $this->defaults->asymmetric,
            $this->keys->encryption()->public,
            $encapsulated_keys,
            $message,
        );
    }
}
