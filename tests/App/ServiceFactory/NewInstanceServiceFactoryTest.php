<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceFactory\NewInstanceServiceFactory;
use PhoneBurner\Pinch\Component\Tests\Fixtures\StaticServiceFactoryTestClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NewInstanceServiceFactoryTest extends TestCase
{
    private App&MockObject $app;

    protected function setUp(): void
    {
        $this->app = $this->createMock(App::class);
    }

    #[Test]
    public function createsNewInstance(): void
    {
        $factory = NewInstanceServiceFactory::singleton();

        $result = $factory($this->app, StaticServiceFactoryTestClass::class);

        self::assertInstanceOf(StaticServiceFactoryTestClass::class, $result);
        self::assertSame('default', $result->getValue());
    }
}
