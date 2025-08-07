<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT | \Attribute::IS_REPEATABLE)]
final readonly class MockRepeatableEnumAttribute
{
    public function __construct(public string $value)
    {
    }
}
