<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<float|null>
 */
final readonly class NullableFloatContextToggle implements ContextToggle
{
    public function __construct(
        public float|null $http = null,
        public float|null $cli = null,
        public float|null $test = null,
    ) {
    }

    public function __invoke(Context $context): float|null
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
