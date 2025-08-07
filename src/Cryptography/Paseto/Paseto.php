<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoCryptoException;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * String (but not BinaryString) wrapper around a Paseto token.
 */
final class Paseto implements \Stringable
{
    public const Encoding ENCODING = Encoding::Base64UrlNoPadding;

    public const string REGEX = '/^(v[1-4])\.(local|public)\.[A-Za-z0-9\-_]+(?:\.([A-Za-z0-9\-_]+))?$/';

    public PasetoVersion $version;

    public PasetoPurpose $purpose;

    private readonly string $footer;

    public function __construct(
        #[\SensitiveParameter] public readonly string $value,
    ) {
        if (! \preg_match(self::REGEX, $this->value, $matches)) {
            throw new PasetoCryptoException('Invalid PASETO Token');
        }

        $this->version = PasetoVersion::from($matches[1]);
        $this->purpose = PasetoPurpose::from($matches[2]);
        $this->footer = $matches[3] ?? '';
    }

    public function token(): self
    {
        return $this;
    }

    public function footer(): string
    {
        return $this->footer ? ConstantTimeEncoder::decode(self::ENCODING, $this->footer) : '';
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
