<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<int>
 */
final readonly class IntContextToggle implements ContextToggle
{
    public function __construct(
        public int $http = 0,
        public int $cli = 0,
        public int $test = 0,
    ) {
    }

    public function __invoke(Context $context): int
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
