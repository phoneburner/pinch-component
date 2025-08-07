<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\IpAddress\Exception;

use PhoneBurner\Pinch\Attribute\Usage\Contract;

#[Contract]
class InvalidIpAddress extends \UnexpectedValueException
{
}
