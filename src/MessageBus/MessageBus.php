<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\MessageBus;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
interface MessageBus
{
    public const string DEFAULT = 'default_bus';

    public function dispatch(object $message): object;
}
