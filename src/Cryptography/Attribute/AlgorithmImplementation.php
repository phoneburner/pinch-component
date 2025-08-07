<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Attribute;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricEncryptionAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SymmetricEncryptionAlgorithm;

#[\Attribute]
final readonly class AlgorithmImplementation
{
    public function __construct(public AsymmetricEncryptionAlgorithm|SymmetricEncryptionAlgorithm $algorithm)
    {
    }
}
