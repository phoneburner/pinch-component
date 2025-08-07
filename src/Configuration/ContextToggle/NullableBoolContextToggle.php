<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<bool|null>
 */
final readonly class NullableBoolContextToggle implements ContextToggle
{
    public function __construct(
        public bool|null $http = null,
        public bool|null $cli = null,
        public bool|null $test = null,
    ) {
    }

    public function __invoke(Context $context): bool|null
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
