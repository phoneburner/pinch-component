<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Mailer;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\EmailAddress\EmailAddress;

#[Contract]
interface Mailable
{
    /**
     * @return array<EmailAddress>
     */
    public function getTo(): array;

    public function getSubject(): string;

    public function getBody(): MessageBody|null;
}
