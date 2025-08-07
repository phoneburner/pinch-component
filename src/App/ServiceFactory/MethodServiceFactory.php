<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\ServiceFactory;
use Psr\Container\ContainerInterface;

/**
 * Create a service from a by calling a method on an object. If a class-string
 * is passed, the instance will be resolved from the container.
 */
final readonly class MethodServiceFactory implements ServiceFactory
{
    /**
     * @param class-string|object $class_or_object
     */
    public function __construct(
        private object|string $class_or_object,
        private string $method = 'make',
    ) {
    }

    public function __invoke(ContainerInterface $app, string $id): object
    {
        $object = \is_string($this->class_or_object) ? $app->get($this->class_or_object) : $this->class_or_object;
        \assert(\is_object($object) && \method_exists($object, $this->method));
        return $object->{$this->method}($app);
    }
}
