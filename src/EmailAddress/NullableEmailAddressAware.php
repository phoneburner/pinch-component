<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\EmailAddress;

use PhoneBurner\Pinch\Attribute\Usage\Contract;
use PhoneBurner\Pinch\Component\EmailAddress\EmailAddress;

#[Contract]
interface NullableEmailAddressAware
{
    public function getEmailAddress(): EmailAddress|null;
}
