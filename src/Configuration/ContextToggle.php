<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

/**
 * @template T
 */
interface ContextToggle
{
    /**
     * @return T
     */
    public function __invoke(Context $context): mixed;
}
