<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricAlgorithm;

class Defaults
{
    public function __construct(
        public SymmetricAlgorithm $symmetric = SymmetricAlgorithm::Aegis256,
        public AsymmetricAlgorithm $asymmetric = AsymmetricAlgorithm::X25519Aegis256,
    ) {
    }
}
