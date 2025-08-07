<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory\DeferredServiceFactory;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockServiceFactory;
use PhoneBurner\Pinch\Component\Tests\Fixtures\ServiceFactoryTestClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeferredServiceFactoryTest extends TestCase
{
    #[Test]
    public function resolutionOfTheWrappedServiceFactoryIsDeferred(): void
    {
        $service = new ServiceFactoryTestClass();

        $app = $this->createMock(App::class);
        $app->expects($this->once())
            ->method('get')
            ->with(MockServiceFactory::class)
            ->willReturn(new MockServiceFactory($service, ServiceFactoryTestClass::class));

        $sut = new DeferredServiceFactory(MockServiceFactory::class);

        self::assertSame($service, $sut($app, ServiceFactoryTestClass::class));
    }
}
