<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<string>
 */
final readonly class StringContextToggle implements ContextToggle
{
    public function __construct(
        public string $http = '',
        public string $cli = '',
        public string $test = '',
    ) {
    }

    public function __invoke(Context $context): string
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
