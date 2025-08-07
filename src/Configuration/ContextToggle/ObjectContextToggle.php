<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\ContextToggle;

use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\ContextToggle;

/**
 * @template T of object
 * @implements ContextToggle<T>
 */
final readonly class ObjectContextToggle implements ContextToggle
{
    /**
     * @param T $http
     * @param T $cli
     * @param T $test
     */
    public function __construct(
        public object $http,
        public object $cli,
        public object $test,
    ) {
    }

    /**
     * @return T
     */
    public function __invoke(Context $context): object
    {
        return match ($context) {
            Context::Http => $this->http,
            Context::Cli => $this->cli,
            Context::Test => $this->test,
        };
    }
}
