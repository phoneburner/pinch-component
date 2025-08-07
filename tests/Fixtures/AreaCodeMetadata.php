<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Component\PhoneNumber\AreaCode\AreaCodePurpose;

readonly class AreaCodeMetadata
{
    public function __construct(
        public int $npa,
        public int $status,
        public AreaCodePurpose $purpose,
        public string $region,
        public array $subdivisions,
        public array $time_zones,
        public bool $is_active,
        public bool $is_activating,
    ) {
    }
}
