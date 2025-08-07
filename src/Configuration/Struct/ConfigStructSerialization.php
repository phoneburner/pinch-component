<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\Struct;

use PhoneBurner\Pinch\Component\Configuration\ConfigStruct;

/**
 * @phpstan-require-implements ConfigStruct
 */
trait ConfigStructSerialization
{
    public function __serialize(): array
    {
        return \get_object_vars($this);
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function __unserialize(array $data): void
    {
        $this->__construct(...$data);
    }
}
