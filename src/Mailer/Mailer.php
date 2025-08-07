<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Mailer;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
interface Mailer
{
    public function send(MailableMessage $message): void;
}
