<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App;

use PhoneBurner\Pinch\Container\AutowiringContainer;
use PhoneBurner\Pinch\Container\InvokingContainer;
use PhoneBurner\Pinch\Container\MutableContainer;
use Psr\Log\LoggerAwareInterface;

/**
 * @extends AutowiringContainer<object>
 * @extends MutableContainer<object>
 */
interface ServiceContainer extends
    MutableContainer,
    InvokingContainer,
    AutowiringContainer,
    LoggerAwareInterface
{
    /**
     * We have to redeclare this method here, even though it is already defined
     * in the parent interfaces to resolve the difference in the method signature
     * arity. Otherwise, PHP triggers a fatal error.
     */
    public function has(\Stringable|string $id, bool $strict = false): bool;

    /**
     * We have to redeclare this method here, even though it is already defined
     * in the parent interfaces to resolve the difference in the method signature
     * arity. Otherwise, PHP triggers a fatal error.
     *
     * @template T of object
     * @return ($id is class-string<T> ? T : never)
     * @phpstan-assert class-string<T> $id
     */
    public function get(\Stringable|string $id): object;
}
