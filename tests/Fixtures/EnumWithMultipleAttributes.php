<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Component\Tests\Fixtures\Attributes\MockClassConstantAttribute;
use PhoneBurner\Pinch\Component\Tests\Fixtures\Attributes\MockRepeatableEnumAttribute;

enum EnumWithMultipleAttributes: string
{
    #[MockRepeatableEnumAttribute('Multi A 1')]
    #[MockClassConstantAttribute('Multi A 2')]
    #[MockRepeatableEnumAttribute('Multi A 3')]
    case CaseA = 'a';

    #[MockClassConstantAttribute('Multi B 1')]
    #[MockRepeatableEnumAttribute('Multi B 2')]
    case CaseB = 'b';
}
