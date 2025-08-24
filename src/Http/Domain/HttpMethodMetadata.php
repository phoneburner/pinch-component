<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Domain;

use PhoneBurner\Pinch\Attribute\Usage\Internal;

#[Internal]
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
final readonly class HttpMethodMetadata
{
    public function __construct(public bool $pure, public bool $idempotent, public bool $cacheable)
    {
    }
}
