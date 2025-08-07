<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Session;

use PhoneBurner\Pinch\Component\Cryptography\String\FixedLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringFromRandomBytes;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * The session id is a 256-bit random string that is used to identify a CSRF token
 *
 * Note: this class is intentionally not readonly to allow for the sensitive token
 * value to be overwritten in memory when the object is destroyed.
 */
final class CsrfToken extends FixedLengthSensitiveBinaryString
{
    use BinaryStringFromRandomBytes;
    use BinaryStringImportBehavior;

    public const int LENGTH = 32; // 256-bit string

    /**
     * The CSRF Token finds its way into a couple different places where having
     * non-url friendly characters, including trailing padding, would be a pain.
     */
    public const Encoding DEFAULT_ENCODING = Encoding::Base64UrlNoPadding;
}
