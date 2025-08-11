<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\Struct;

use PhoneBurner\Pinch\Component\Configuration\ConfigStruct;

use function PhoneBurner\Pinch\Type\cast_nullable_nonempty_string;

/**
 * General purpose configuration struct for something like an API key, enforcing
 * either a non-empty string or null value.
 */
abstract readonly class ApiKeyConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @var non-empty-string|null
     */
    public string|null $api_key;

    public function __construct(#[\SensitiveParameter] string|null $api_key)
    {
        $this->api_key = cast_nullable_nonempty_string($api_key);
    }
}
