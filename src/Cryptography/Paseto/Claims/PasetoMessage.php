<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims;

/**
 * Note, the Paseto Specification does not restrict the footer to be a JSON object,
 * and it could be an arbitrary string. However, this implementation assumes that
 * the footer is a JSON object, and it will throw an exception if the footer is not
 * a valid JSON object.
 */
final class PasetoMessage
{
    public function __construct(
        public string $payload,
        public string $footer = '',
    ) {
    }

    /**
     * @param PasetoPayloadClaims|array<string, mixed> $payload
     * @param PasetoFooterClaims|array<string, mixed> $footer
     */
    public static function make(PasetoPayloadClaims|array $payload = [], PasetoFooterClaims|array $footer = []): self
    {
        return new self(
            $payload === [] ? '' : \json_encode($payload, \JSON_THROW_ON_ERROR),
            match (true) {
                $footer === [], $footer instanceof PasetoFooterClaims && $footer->isEmpty() => '',
                default => \json_encode($footer, \JSON_THROW_ON_ERROR),
            },
        );
    }

    public function payload(): DecodedPayloadClaims
    {
        return DecodedPayloadClaims::make($this->payload);
    }

    public function footer(): DecodedFooterClaims
    {
        return DecodedFooterClaims::make($this->footer);
    }
}
