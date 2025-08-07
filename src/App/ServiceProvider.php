<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\App\App;

#[Contract]
interface ServiceProvider
{
    /**
     * Return a map of interfaces to implementations that this service provider
     * provides to the container. This is used to automatically bind interfaces to
     * implementations in the container.
     *
     * @return array<class-string, class-string>
     */
    public static function bind(): array;

    /**
     * Register application services with the container. This step should not
     * have side effects, and should only bind service definitions to the container.
     */
    public static function register(App $app): void;
}
