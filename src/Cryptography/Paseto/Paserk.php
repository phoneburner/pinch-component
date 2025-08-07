<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureSecretKey;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoCryptoException;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\String\Encoding\ConstantTimeEncoder;

final class Paserk implements \Stringable
{
    private function __construct(
        public readonly PaserkVersion $version,
        public readonly PaserkType $type,
        private string $data,
        public string|null $prefix = null,
    ) {
        match ($this->type->metadata()->prefix) {
            true => $this->prefix !== null || throw new PasetoCryptoException('Prefix is not allowed for this PASERK type'),
            false => $this->prefix === null || throw new PasetoCryptoException('Prefix is required for this PASERK type'),
        };
    }

    /**
     * Overwrite the data in memory with null bytes and internally set the value
     * to null when the object is destroyed. This is to prevent any keys from leaking
     * into memory dumps or overflows. Doing this requires that the class not be
     * marked as readonly.
     */
    public function __destruct()
    {
        /** @phpstan-ignore isset.initializedProperty */
        if (isset($this->data)) {
            /** @phpstan-ignore assign.propertyType */
            \sodium_memzero($this->data);
        }
    }

    public static function import(\Stringable|string $paserk): self
    {
        $parts = \explode('.', (string)$paserk, 4);
        return match (\count($parts)) {
            3 => new self(PaserkVersion::from($parts[0]), PaserkType::from($parts[1]), $parts[2]),
            4 => new self(PaserkVersion::from($parts[0]), PaserkType::from($parts[1]), $parts[3], $parts[2]),
            default => throw new PasetoCryptoException('Invalid PASERK format (expected 3 or 4 parts)'),
        };
    }

    public static function tryImport(\Stringable|string $paserk): self|null
    {
        try {
            return self::import($paserk);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function local(
        SharedKey $shared_key,
        PaserkVersion $version = PaserkVersion::V4,
    ): self {
        return new self(
            $version,
            PaserkType::Local,
            $shared_key->export(Paseto::ENCODING),
        );
    }

    public static function lid(self|SharedKey $paserk): self
    {
        return self::id(PaserkType::LocalId, $paserk instanceof self ? $paserk : self::local($paserk));
    }

    public static function public(
        SignaturePublicKey|SignatureKeyPair $public_key,
        PaserkVersion $version = PaserkVersion::V4,
    ): self {
        return new self(
            $version,
            PaserkType::Public,
            $public_key->public()->export(Paseto::ENCODING),
        );
    }

    public static function pid(self|SignaturePublicKey|SignatureKeyPair $paserk): self
    {
        return self::id(PaserkType::PublicId, $paserk instanceof self ? $paserk : self::public($paserk));
    }

    public static function secret(
        SignatureSecretKey|SignatureKeyPair $secret_key,
        PaserkVersion $version = PaserkVersion::V4,
    ): self {
        return new self(
            $version,
            PaserkType::Secret,
            $secret_key->secret()->export(Paseto::ENCODING),
        );
    }

    public static function sid(self|SignatureSecretKey|SignatureKeyPair $paserk): self
    {
        return self::id(PaserkType::SecretId, $paserk instanceof self ? $paserk : self::secret($paserk));
    }

    public function __toString(): string
    {
        return $this->prefix === null
            ? \sprintf("%s.%s.%s", $this->version->value, $this->type->value, $this->data)
            : \sprintf("%s.%s.%s.%s", $this->version->value, $this->type->value, $this->prefix, $this->data);
    }

    private static function id(PaserkType $expected_type, self $paserk): self
    {
        if ($expected_type->metadata()->id !== null) {
            throw new PasetoCryptoException(\sprintf('PASERK type "%s" is not an ID', $expected_type->value));
        }

        $type = $paserk->type->metadata()->id ?? throw new PasetoCryptoException(
            \sprintf('Invalid PASERK type "%s" for ID generation', $paserk->type->value),
        );

        if ($type !== $expected_type) {
            throw new PasetoCryptoException(\sprintf(
                'PASERK type %s corresponds to %s, expected %s',
                $paserk->type->value,
                $type->value,
                $expected_type->value,
            ));
        }

        return new self($paserk->version, $type, ConstantTimeEncoder::encode(
            Paseto::ENCODING,
            \sodium_crypto_generichash($paserk->version->value . '.' . $type->value . '.' . $paserk, length: 33),
        ));
    }
}
