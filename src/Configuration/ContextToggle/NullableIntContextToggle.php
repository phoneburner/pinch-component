<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<int|null>
 */
final readonly class NullableIntContextToggle implements ContextToggle
{
    public function __construct(
        public int|null $http = null,
        public int|null $cli = null,
        public int|null $test = null,
    ) {
    }

    public function __invoke(Context $context): int|null
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
