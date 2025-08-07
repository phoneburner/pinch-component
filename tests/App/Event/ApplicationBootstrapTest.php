<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\App\Event;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\Event\ApplicationBootstrap;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApplicationBootstrapTest extends TestCase
{
    #[Test]
    public function constructorSetsAppProperty(): void
    {
        $app = $this->createMock(App::class);
        self::assertSame($app, new ApplicationBootstrap($app)->app);
    }
}
