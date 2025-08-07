<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber\Exception;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
class InvalidPhoneNumber extends \UnexpectedValueException
{
}
