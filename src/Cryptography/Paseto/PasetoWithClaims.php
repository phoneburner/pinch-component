<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoFooterClaims;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoPayloadClaims;

readonly class PasetoWithClaims implements \Stringable
{
    public function __construct(
        public Paseto $token,
        public PasetoPayloadClaims $payload,
        public PasetoFooterClaims $footer,
    ) {
    }

    public function token(): Paseto
    {
        return $this->token;
    }

    public function __toString(): string
    {
        return (string)$this->token;
    }
}
