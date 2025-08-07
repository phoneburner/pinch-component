<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

enum PasetoVersion: string
{
    case V1 = 'v1';
    case V2 = 'v2';
    case V3 = 'v3';
    case V4 = 'v4';
}
