<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

use PhoneBurner\Pinch\Component\Cryptography\Paseto\Attribute\PaserkTypeMetadata;

use function PhoneBurner\Pinch\Enum\case_attr_fetch;

enum PaserkType: string
{
    /**
     * Unique identifier for a PASERK describing a `local` (shared/symmetric) key
     */
    #[PaserkTypeMetadata(id: null, purpose: PasetoPurpose::Local, allowed_in_footer: true)]
    case LocalId = 'lid';

    /**
     * Unique identifier for a PASERK describing a `public` (asymmetric) key
     */
    #[PaserkTypeMetadata(id: null, purpose: PasetoPurpose::Public, allowed_in_footer: true)]
    case PublicId = 'pid';

    /**
     * Unique identifier for a PASERK describing a `secret` (asymmetric) key
     */
    #[PaserkTypeMetadata(id: null, purpose: PasetoPurpose::Public, allowed_in_footer: true)]
    case SecretId = 'sid';

    /**
     * Symmetric shared key for local tokens
     */
    #[PaserkTypeMetadata(id: self::LocalId, purpose: PasetoPurpose::Local, allowed_in_footer: false)]
    case Local = 'local';

    /**
     * Symmetric key wrapped with asymmetric encryption
     */
    #[PaserkTypeMetadata(id: self::LocalId, purpose: PasetoPurpose::Local, allowed_in_footer: true)]
    case Seal = 'seal';

    /**
     * Symmetric key wrapped with symmetric encryption using another symmetric key
     */
    #[PaserkTypeMetadata(id: self::LocalId, purpose: PasetoPurpose::Local, allowed_in_footer: true, prefix: true)]
    case LocalWrap = 'local-wrap';

    /**
     * Symmetric key wrapped with symmetric encryption using a password
     */
    #[PaserkTypeMetadata(id: self::LocalId, purpose: PasetoPurpose::Local, allowed_in_footer: false)]
    case LocalPassword = 'local-pw';

    /**
     * Asymmetric public key used for verifying `public` tokens.
     */
    #[PaserkTypeMetadata(id: self::PublicId, purpose: PasetoPurpose::Public, allowed_in_footer: false)]
    case Public = 'public';

    /**
     * Asymmetric secret key for signing `public` tokens.
     */
    #[PaserkTypeMetadata(id: self::SecretId, purpose: PasetoPurpose::Public, allowed_in_footer: false)]
    case Secret = 'secret';

    /**
     * Asymmetric secret key wrapped with symmetric encryption using a symmetric key
     */
    #[PaserkTypeMetadata(id: self::SecretId, purpose: PasetoPurpose::Public, allowed_in_footer: true, prefix: true)]
    case SecretWrap = 'secret-wrap';

    /**
     * Asymmetric secret key wrapped with symmetric encryption using a password
     */
    #[PaserkTypeMetadata(id: self::SecretId, purpose: PasetoPurpose::Public, allowed_in_footer: false)]
    case SecretPassword = 'secret-pw';

    public function metadata(): PaserkTypeMetadata
    {
        static $metadata = [];
        return $metadata[$this->name] ??= case_attr_fetch($this, PaserkTypeMetadata::class);
    }
}
