<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App;

use PhoneBurner\Pinch\Component\Configuration\Configuration;
use PhoneBurner\Pinch\Component\Configuration\ConfigurationFactory;
use PhoneBurner\Pinch\Component\Configuration\Environment;
use PhoneBurner\Pinch\Container\AutowiringContainer;
use PhoneBurner\Pinch\Container\InvokingContainer;
use PhoneBurner\Pinch\Container\MutableContainer;

/**
 * This is the main application class. It is a container that holds context,
 * environment state, configuration, and services. It should be the only singleton
 * service in the application, so that tearing it can result in complete garbage
 * collection and reduce the possibility of memory leaks or stale/shared state.
 *
 * While the class is a container, it is not intended to be used as a general-purpose
 * service container itself. The implemented container methods are really shortcuts to
 * the underlying service container.
 *
 * @extends AutowiringContainer<object>
 * @extends MutableContainer<object>
 */
interface App extends AutowiringContainer, MutableContainer, InvokingContainer
{
    // phpcs:ignore
    public Environment $environment { get; }

    // phpcs:ignore
    public ServiceContainer $services { get; }

    // phpcs:ignore
    public Configuration $config { get; }

    public static function booted(): bool;

    public static function instance(): self;

    /**
     * Under normal usage, only the Environment should be passed to this method.
     * The optional Configuration and ServiceContainer parameters are intended to
     * allow testing of the application lifecycle without needing hard code config
     * files or service providers.
     */
    public static function bootstrap(
        Environment $environment,
        ConfigurationFactory|Configuration|null $config = null,
        ServiceContainerFactory|ServiceContainer|null $services = null,
    ): self;

    public static function teardown(): null;

/**
     * Wrap a callback in the context of an application lifecycle instance. Note
     * that if exit() is called within the callback, the application will still be
     * torn down properly because App::teardown(...) is registered as a shutdown
     * function.
     *
     * @template TReturn
     * @param callable(self): TReturn $callback
     * @return TReturn
     */
    public static function exec(Environment $environment, callable $callback): mixed;

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
     */
    public function get(\Stringable|string $id): object;
}
