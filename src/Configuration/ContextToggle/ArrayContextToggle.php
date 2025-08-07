<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T of array
 * @implements ContextToggle<T>
 */
final readonly class ArrayContextToggle implements ContextToggle
{
    /**
     * @param T $http
     * @param T $cli
     * @param T $test
     */
    public function __construct(
        public array $http = [],
        public array $cli = [],
        public array $test = [],
    ) {
    }

    /**
     * @return T
     */
    public function __invoke(Context $context): array
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
