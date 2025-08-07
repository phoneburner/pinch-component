<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\ServiceFactory;
use Psr\Container\ContainerInterface;

/**
 * Create a service from a by calling a method on a class or object.
 */
final readonly class StaticMethodServiceFactory implements ServiceFactory
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
        \assert(\method_exists($this->class_or_object, $this->method));
        return $this->class_or_object::{$this->method}($app);
    }
}
