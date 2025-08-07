<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Enum\Trait\WithUnitEnumInstanceStaticMethod;

enum UnitStoplightState
{
    use WithUnitEnumInstanceStaticMethod;

    case Red;
    case Yellow;
    case Green;
}
