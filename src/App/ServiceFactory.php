<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\App;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\App\App;

#[Contract]
interface ServiceFactory
{
    /**
     * @param class-string $id
     */
    public function __invoke(App $app, string $id): object;
}
