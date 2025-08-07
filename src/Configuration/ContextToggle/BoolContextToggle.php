<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<bool>
 */
final readonly class BoolContextToggle implements ContextToggle
{
    public function __construct(
        public bool $http = false,
        public bool $cli = false,
        public bool $test = false,
    ) {
    }

    public function __invoke(Context $context): bool
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
