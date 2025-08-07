<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Session;

use PhoneBurner\Pinch\Component\Cryptography\String\FixedLengthSensitiveBinaryString;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringFromRandomBytes;
use PhoneBurner\Pinch\String\BinaryString\Traits\BinaryStringImportBehavior;
use PhoneBurner\Pinch\String\Encoding\Encoding;

/**
 * The session id is a 256-bit random string that is used to identify a session.
 *
 * Note: this class is intentionally not readonly to allow for the sensitive id
 * value to be overwritten in memory when the object is destroyed.
 */
final class SessionId extends FixedLengthSensitiveBinaryString
{
    use BinaryStringFromRandomBytes;
    use BinaryStringImportBehavior;

    public const int LENGTH = 32; // 256-bit string

    /**
     * Hexadecimal encoding is used here instead of our standard base64url encoding to
     * avoid problems with the session id being used as part of a cache key. The base64url
     * encoding is safe for URLs and file names, but it is not safe for PSR-6 cache keys.
     * Our cache implementation replaces invalid characters in the cache key with '_',
     * which would reduce the entropy of the session id, making collisions or potential
     * bugs more likely. The conversion would not be reversible, so we would not be able
     * to recover the original session id from the cache key. So, hex it is.
     */
    public const Encoding DEFAULT_ENCODING = Encoding::Hex;
}
