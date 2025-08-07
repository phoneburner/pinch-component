<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Logging;

use PhoneBurner\Pinch\Enum\Trait\WithStringBackedInstanceStaticMethod;
use Psr\Log\LogLevel as Psr3LogLevel;

enum LogLevel: string
{
    use WithStringBackedInstanceStaticMethod;

    case Emergency = Psr3LogLevel::EMERGENCY;
    case Alert = Psr3LogLevel::ALERT;
    case Critical = Psr3LogLevel::CRITICAL;
    case Error = Psr3LogLevel::ERROR;
    case Warning = Psr3LogLevel::WARNING;
    case Notice = Psr3LogLevel::NOTICE;
    case Info = Psr3LogLevel::INFO;
    case Debug = Psr3LogLevel::DEBUG;

    private const array MONOLOG_MAP = [
        600 => self::Emergency,
        550 => self::Alert,
        500 => self::Critical,
        400 => self::Error,
        300 => self::Warning,
        250 => self::Notice,
        200 => self::Info,
        100 => self::Debug,
    ];

    public static function instance(mixed $value): self
    {
        return match (true) {
            $value instanceof self => $value,
            \is_object($value) && \method_exists($value, 'toPsrLogLevel') => self::from($value->toPsrLogLevel()),
            \is_string($value) => self::from(\strtolower($value)),
            \is_int($value) => self::MONOLOG_MAP[$value] ?? throw new \InvalidArgumentException(),
            default => throw new \InvalidArgumentException(),
        };
    }

    public function toMonlogLogLevel(): int
    {
        return match ($this) {
            self::Emergency => 600,
            self::Alert => 550,
            self::Critical => 500,
            self::Error => 400,
            self::Warning => 300,
            self::Notice => 250,
            self::Info => 200,
            self::Debug => 100,
        };
    }
}
