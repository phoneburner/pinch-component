<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory;
use PhoneBurner\Pinch\Component\Configuration\ConfigStruct;

use function PhoneBurner\Pinch\Type\narrow;

final readonly class ConfigStructServiceFactory implements ServiceFactory
{
    public function __construct(private string $name)
    {
    }

    public function __invoke(App $app, string $id): ConfigStruct
    {
        return narrow(ConfigStruct::class, $app->config->get($this->name));
    }
}
