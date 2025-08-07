<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory;

final readonly class CallableServiceFactory implements ServiceFactory
{
    public function __construct(private \Closure $closure)
    {
    }

    public function __invoke(App $app, string $id): object
    {
        return ($this->closure)($app);
    }
}
