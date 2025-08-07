<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

use PhoneBurner\Pinch\Enum\Trait\WithStringBackedInstanceStaticMethod;

enum BuildStage: string
{
    use WithStringBackedInstanceStaticMethod;

    case Production = 'production';
    case Integration = 'integration';
    case Development = 'development';
}
