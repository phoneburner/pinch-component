# PASETO: Platform-Agnostic Security Tokens

PASETO tokens fill a similar role to JSON Web Tokens (JWT), but are designed to
be easier to use, with security enforced by the protocol itself. For example, where
JWTs support many different algorithms for signing the tokens, with various security
properties -- including the incredibly problematic `none` algorithm, PASETO tokens
are strictly versioned and, each version only defines a single well-defined and
secure algorithm purpose.

A PASETO token can have one of two "purposes":

- _local_: the token is encrypted (symmetric-key, AEAD) and can only be read by
  the party that encrypted it. This is useful for tokens that contain sensitive
  information that should not be exposed to the client.

- _public_: the token is signed, but not encrypted, and can be read by anyone, and
  anyone with the public key of the signer can verify that the token has not been
  tampered with and was signed by the expected party. This is useful for tokens
  that contain information that could be visible to the client and should be the
  default choice for most use-cases, where JWTs would be used.

"Low-level" implementations of the PASETO spec are available in the
`PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol` namespace; however,
most users will want to use the `Natrium` cryptography facade to interact with PASETO.
The low-level implementations are provided for users who need to interact with PASETO
directly, and are intended to implement the PASETO spec as closely as possible, while
we enforce some additional assumptions/requirements on the high-level `Natrium`.

### Differences from the PASETO Spec

- Only the `v2` (Sodium Original) and `v4` (Sodium Modern) versions of the spec
  are supported, `v4` is used for all new tokens.
- The PASETO spec allows the optional footer to be an arbitrary string, including
  JSON, but we require that the footer to always be a JSON object if it is present.
- All tokens MUST define a `iat` (issued at time) and `nbf` (not before time) claims.
- All tokens MUST define a `exp` (expiration time) claim, and the TTL of the token
  (compared to the `iat` claim) MUST NOT exceed 366 days.

Note: See `\PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\RegisteredPayloadClaim`
and `\PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\RegisteredFooterClaim` for
the registered claims that are supported by the PASETO spec. Custom claims are
allowed, but where ever possible the standard [IANA registered JWT claims](https://www.iana.org/assignments/jwt/jwt.xhtml#claims) should be used.

### Including the Public Key in the Token

> Why not just include the public key as the kid or other claim in the token footer?

The PASETO spec requires that key identifiers MUST be independent of the token.
Including the public key in the footer of a public token encourages the recipient
to use that value to verify the token, completely bypassing the security of
public-key cryptography. Instead, the recipient should have a trusted source for
the public key and use that to verify the token.

> Why not use a cryptographic hash of the shared/secret/public key as the key identifier?

A hash does not provide any assurance of confidentiality, and there is a risk that
the algorithm used to hash the key could leak some kind of state information about
the key. Instead, the key identifier should be a random value that is unique to the
key and is not derived from the key itself.
