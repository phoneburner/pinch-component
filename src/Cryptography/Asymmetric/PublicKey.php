<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\Key;

interface PublicKey extends Key
{
    public function public(): static;
}
