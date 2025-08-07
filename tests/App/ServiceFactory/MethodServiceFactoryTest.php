<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\ServiceFactory\MethodServiceFactory;
use PhoneBurner\Pinch\Component\Tests\Fixtures\ServiceFactoryTestClass;
use PhoneBurner\Pinch\Component\Tests\Fixtures\StaticServiceFactoryTestClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class MethodServiceFactoryTest extends TestCase
{
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    #[Test]
    public function invokesDefaultMakeMethod(): void
    {
        $factory = new MethodServiceFactory(StaticServiceFactoryTestClass::class);
        $service = new StaticServiceFactoryTestClass('from make');
        $this->container->method('get')->with(StaticServiceFactoryTestClass::class)->willReturn($service);

        $result = $factory($this->container, StaticServiceFactoryTestClass::class);

        self::assertInstanceOf(StaticServiceFactoryTestClass::class, $result);
        self::assertSame('from make', $result->getValue());
    }

    #[Test]
    public function invokesSpecifiedMethod(): void
    {
        $factory = new MethodServiceFactory(StaticServiceFactoryTestClass::class, 'create');
        $service = new StaticServiceFactoryTestClass('from create');
        $this->container->method('get')->with(StaticServiceFactoryTestClass::class)->willReturn($service);

        $result = $factory($this->container, StaticServiceFactoryTestClass::class);

        self::assertInstanceOf(StaticServiceFactoryTestClass::class, $result);
        self::assertSame('from create', $result->getValue());
    }

    #[Test]
    public function throwsWhenServiceNotFound(): void
    {
        $factory = new MethodServiceFactory(StaticServiceFactoryTestClass::class);
        $this->container->method('get')->with(StaticServiceFactoryTestClass::class)->willReturn(null);

        $this->expectException(\AssertionError::class);
        $factory($this->container, StaticServiceFactoryTestClass::class);
    }

    #[Test]
    public function invokesDefaultMakeNonStaticMethod(): void
    {
        $factory = new MethodServiceFactory(ServiceFactoryTestClass::class);
        $service = new ServiceFactoryTestClass('from make');
        $this->container->method('get')->with(ServiceFactoryTestClass::class)->willReturn($service);

        $result = $factory($this->container, ServiceFactoryTestClass::class);

        self::assertInstanceOf(ServiceFactoryTestClass::class, $result);
        self::assertSame('from make', $result->getValue());
    }

    #[Test]
    public function invokesSpecifiedNonStaticMethod(): void
    {
        $factory = new MethodServiceFactory(ServiceFactoryTestClass::class, 'create');
        $service = new ServiceFactoryTestClass('from create');
        $this->container->method('get')->with(ServiceFactoryTestClass::class)->willReturn($service);

        $result = $factory($this->container, ServiceFactoryTestClass::class);

        self::assertInstanceOf(ServiceFactoryTestClass::class, $result);
        self::assertSame('from create', $result->getValue());
    }

    #[Test]
    public function throwsWhenServiceNotFoundNonStatic(): void
    {
        $factory = new MethodServiceFactory(ServiceFactoryTestClass::class);
        $this->container->method('get')->with(ServiceFactoryTestClass::class)->willReturn(null);

        $this->expectException(\AssertionError::class);
        $factory($this->container, ServiceFactoryTestClass::class);
    }
}
