<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\Key;

interface KeyPair extends Key
{
    public function secret(): SecretKey;

    public function public(): PublicKey;

    public static function generate(): static;
}
