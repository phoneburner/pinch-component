<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T
 * @implements ContextToggle<array<T>|null>
 */
final readonly class NullableArrayContextToggle implements ContextToggle
{
    /**
     * @param array<mixed>|null $http
     * @param array<mixed>|null $cli
     * @param array<mixed>|null $test
     */
    public function __construct(
        public array|null $http = null,
        public array|null $cli = null,
        public array|null $test = null,
    ) {
    }

    /**
     * @return array<mixed>|null
     */
    public function __invoke(Context $context): array|null
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
