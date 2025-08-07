<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Enum\Trait\WithStringBackedInstanceStaticMethod;
use PhoneBurner\Pinch\Enum\Trait\WithValuesStaticMethod;

enum StoplightState: string
{
    use WithStringBackedInstanceStaticMethod;
    use WithValuesStaticMethod;

    case Red = 'red';
    case Yellow = 'yellow';
    case Green = 'green';
}
