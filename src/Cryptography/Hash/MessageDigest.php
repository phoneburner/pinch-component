<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\Hash;

use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * Implementing classes MUST return the lower-case hexit representation of the
 * hash digest when the __toString() method is called.
 */
interface MessageDigest extends \Stringable
{
    public function algorithm(): HashAlgorithm;

    public function digest(Encoding $encoding = Encoding::Hex): string;
}
