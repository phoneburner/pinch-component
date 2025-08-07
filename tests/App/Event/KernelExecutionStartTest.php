<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\App\Event;

use PhoneBurner\Pinch\Component\App\Event\KernelExecutionStart;
use PhoneBurner\Pinch\Component\App\Kernel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class KernelExecutionStartTest extends TestCase
{
    #[Test]
    public function constructorSetsKernelProperty(): void
    {
        $kernel = $this->createMock(Kernel::class);
        self::assertSame($kernel, new KernelExecutionStart($kernel)->kernel);
    }
}
