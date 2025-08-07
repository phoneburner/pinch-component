<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @implements ContextToggle<mixed>
 */
final readonly class MixedContextToggle implements ContextToggle
{
    public function __construct(
        public mixed $http = null,
        public mixed $cli = null,
        public mixed $test = null,
    ) {
    }

    public function __invoke(Context $context): mixed
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
