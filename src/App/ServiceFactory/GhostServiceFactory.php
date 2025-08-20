<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory;

final readonly class GhostServiceFactory implements ServiceFactory
{
    /**
     * @template T of object
     * @param class-string<T> $class
     * @param \Closure(T): void|\Closure(T): null $initializer
     */
    public function __construct(private string $class, private \Closure $initializer)
    {
    }

    public function __invoke(App $app, string $id): object
    {
        // We want to assert that the class is actually a class and not an interface or trait
        \assert(\class_exists($this->class) && \is_a($this->class, $id, true));
        return new \ReflectionClass($this->class)->newLazyGhost($this->initializer);
    }
}
