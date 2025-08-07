<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Fixtures;

use PhoneBurner\Pinch\Component\Cryptography\Paseto\PaserkVersion;

final readonly class PaserkTestVectorStruct
{
    public function __construct(
        public PaserkVersion $version,
        public string $key,
        public string $secret,
        public string $sid,
        public string $public,
        public string $pid,
        public string $local,
        public string $lid,
    ) {
    }
}
