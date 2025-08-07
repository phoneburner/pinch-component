<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

enum PaserkVersion: string
{
    case V1 = 'k1';
    case V2 = 'k2';
    case V3 = 'k3';
    case V4 = 'k4';
}
