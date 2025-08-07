<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\EmailAddress;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\EmailAddress\EmailAddress;
use PhoneBurner\Pinch\Component\EmailAddress\NullableEmailAddressAware;

#[Contract]
interface EmailAddressAware extends NullableEmailAddressAware
{
    public function getEmailAddress(): EmailAddress;
}
