<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\App\ServiceFactory;

use PhoneBurner\Pinch\Component\App\ServiceFactory\ConfigStructServiceFactory;
use PhoneBurner\Pinch\Component\Configuration\ImmutableConfiguration;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockApp;
use PhoneBurner\Pinch\Component\Tests\Fixtures\TestApiKeyConfigStruct;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConfigStructServiceFactoryTest extends TestCase
{
    #[Test]
    public function itResolvesConfigStructFromApp(): void
    {
        $config_struct = new TestApiKeyConfigStruct('foo');
        $app = new MockApp(config: new ImmutableConfiguration([
            'test' => $config_struct,
        ]));

        $factory = new ConfigStructServiceFactory('test');

        self::assertSame($config_struct, $factory($app, TestApiKeyConfigStruct::class));
    }
}
