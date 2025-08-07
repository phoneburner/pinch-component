<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<float>
 */
final readonly class FloatContextToggle implements ContextToggle
{
    public function __construct(
        public float $http = 0.0,
        public float $cli = 0.0,
        public float $test = 0.0,
    ) {
    }

    public function __invoke(Context $context): float
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
