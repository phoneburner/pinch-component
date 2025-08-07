<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Enum\Trait\WithIntegerBackedInstanceStaticMethod;
use PhoneBurner\Pinch\Enum\Trait\WithValuesStaticMethod;

enum ArabicNumerals: int
{
    use WithIntegerBackedInstanceStaticMethod;
    use WithValuesStaticMethod;

    case Zero = 0;
    case One = 1;
    case Two = 2;
    case Three = 3;
    case Four = 4;
    case Five = 5;
    case Six = 6;
    case Seven = 7;
    case Eight = 8;
    case Nine = 9;
}
