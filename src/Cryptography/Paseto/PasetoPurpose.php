<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Paseto;

enum PasetoPurpose: string
{
    case Local = 'local';
    case Public = 'public';
}
