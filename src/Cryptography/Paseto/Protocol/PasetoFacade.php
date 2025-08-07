<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoLogicException;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoFooterClaims;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoMessage;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoPayloadClaims;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paserk;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoVersion;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoWithClaims;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;

/**
 * PASETO (Platform Agnostic Security Tokens)
 *
 * PASETO is a specification for secure, stateless tokens, similar to JWT/JOSE.
 * Unlike JWT, which is designed around "algorithm agility" and flexibility, and
 * suffers from numerous security defects as a result, PASETO is specifically
 * designed to only allow secure operations and is restricted to a predefined
 * set of versioned protocols, each defining two complete algorithms for both
 * authenticated symmetric-key encryption of the payload ("local") and
 * public-key authentication of plaintext data ("public").
 *
 * Only the latest version of each protocol is supported for the creation of new
 * tokens, but the library is designed to be able to parse and validate tokens
 * from either V2 or V4.
 *
 * Notes:
 * - Both algorithms can use the same original key string, as we use it to
 *   derive both a symmetric key and a keypair for producing public/secret keys.
 * - The specification requires that everything except the token header to be
 *   encoded (strictly) with Base64Url without padding. (https://tools.ietf.org/html/rfc4648#page-8)
 *
 * @link https://github.com/paseto-standard/paseto-spec
 * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version2.md
 * @link https://github.com/paseto-standard/paseto-spec/blob/master/docs/01-Protocol-Versions/Version4.md
 * @link https://github.com/paragonie/paseto
 */
final class PasetoFacade
{
    public function encrypt(
        SharedKey $key,
        PasetoPayloadClaims $payload,
        PasetoFooterClaims $footer = new PasetoFooterClaims(),
        string $additional_data = '',
    ): PasetoWithClaims {
        // Inject a PASERK LID for the public key into the footer so that the
        // recipient can determine which key to use for decryption.
        $footer = new PasetoFooterClaims(Paserk::lid($key), other: $footer->other);
        return new PasetoWithClaims(
            Version4::encrypt($key, PasetoMessage::make($payload, $footer), $additional_data),
            $payload,
            $footer,
        );
    }

    public function decrypt(SharedKey $key, Paseto $token, string $additional_data = ''): PasetoMessage
    {
        return match ($token->version) {
            PasetoVersion::V4 => Version4::decrypt($key, $token, $additional_data),
            PasetoVersion::V2 => Version2::decrypt($key, $token, $additional_data),
            default => throw new PasetoLogicException('Unsupported Paseto Protocol Version'),
        };
    }

    public function sign(
        SignatureKeyPair $key_pair,
        PasetoPayloadClaims $payload,
        PasetoFooterClaims $footer = new PasetoFooterClaims(),
        string $additional_data = '',
    ): PasetoWithClaims {
        // Inject a PASERK PID for the public key into the footer so that the
        // recipient can determine which key to use for verification.
        $footer = new PasetoFooterClaims(Paserk::pid($key_pair), other: $footer->other);
        return new PasetoWithClaims(
            Version4::sign($key_pair, PasetoMessage::make($payload, $footer), $additional_data),
            $payload,
            $footer,
        );
    }

    public function verify(
        SignaturePublicKey $key,
        Paseto $token,
        string $additional_data = '',
    ): PasetoMessage {
        return match ($token->version) {
            PasetoVersion::V4 => Version4::verify($key, $token, $additional_data),
            PasetoVersion::V2 => Version2::verify($key, $token, $additional_data),
            default => throw new PasetoLogicException('Unsupported Paseto Protocol Version'),
        };
    }
}
