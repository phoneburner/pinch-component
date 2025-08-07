<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Component\Tests\Fixtures\Attributes\MockAttribute;

#[MockAttribute('class')]
class ClassWithAttributes
{
    public const string CONSTANT = 'constant';

    #[MockAttribute('property')]
    public string $property = '';

    #[MockAttribute('method')]
    public function method(): void
    {
    }
}
