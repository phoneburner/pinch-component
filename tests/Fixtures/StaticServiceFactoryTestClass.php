<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

final readonly class StaticServiceFactoryTestClass
{
    public function __construct(
        private string $value = 'default',
    ) {
    }

    public static function make(): self
    {
        return new self('from make');
    }

    public static function create(): self
    {
        return new self('from create');
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
