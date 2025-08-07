<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

final readonly class ServiceFactoryTestClass
{
    public function __construct(
        private string $value = 'default',
    ) {
    }

    public function make(): self
    {
        return new self('from make');
    }

    public function create(): self
    {
        return new self('from create');
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
