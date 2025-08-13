<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http;

use PhoneBurner\Pinch\Component\Http\Request\RequestHandlerFactory;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockContainer;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockRequestHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RequestHandlerFactoryTest extends TestCase
{
    private MockContainer $container;
    private RequestHandlerFactory $factory;

    protected function setUp(): void
    {
        $this->container = new MockContainer();
        $this->factory = new RequestHandlerFactory($this->container);
    }

    #[Test]
    public function makeReturnsHandlerWhenGivenInstance(): void
    {
        $handler = new MockRequestHandler();

        $result = $this->factory->make($handler);

        self::assertSame($handler, $result);
    }

    #[Test]
    public function makeResolvesFromContainerWhenGivenClassName(): void
    {
        $handler = new MockRequestHandler();
        $class_name = MockRequestHandler::class;

        $this->container->registerService($class_name, $handler);

        $result = $this->factory->make($class_name);

        self::assertSame($handler, $result);
        self::assertTrue($this->container->wasServiceRequested($class_name));
    }

    #[Test]
    public function makeThrowsWhenGivenInvalidString(): void
    {
        $invalid_class = 'InvalidClass';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(RequestHandlerFactory::TYPE_ERROR);

        $this->factory->make($invalid_class);
    }

    #[Test]
    public function makeThrowsWhenGivenNonHandlerClass(): void
    {
        $non_handler_class = \stdClass::class;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(RequestHandlerFactory::TYPE_ERROR);

        $this->factory->make($non_handler_class);
    }
}
