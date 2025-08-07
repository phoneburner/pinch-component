<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<string|null>
 */
final readonly class NullableStringContextToggle implements ContextToggle
{
    public function __construct(
        public string|null $http = null,
        public string|null $cli = null,
        public string|null $test = null,
    ) {
    }

    public function __invoke(Context $context): string|null
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
