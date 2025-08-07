<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory;

/**
 * Instantiates a new object of the given class, passing the given arguments to
 * the constructor. If the class is not provided in the constructor, we'll use
 * the entry id of the service being resolved by the container.
 */
final readonly class NewInstanceServiceFactory implements ServiceFactory
{
    public static function singleton(): self
    {
        static $instance = new self();
        return $instance;
    }

    public function __invoke(App $app, string $id): object
    {
        return new $id();
    }
}
