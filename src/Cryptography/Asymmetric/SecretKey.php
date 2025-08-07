<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\Key;

interface SecretKey extends Key
{
    public function secret(): static;
}
