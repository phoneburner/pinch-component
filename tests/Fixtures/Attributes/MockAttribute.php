<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class MockAttribute
{
    public function __construct(public string $name = 'test')
    {
    }
}
