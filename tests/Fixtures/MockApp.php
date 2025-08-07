<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Fixtures;

use PhoneBurner\Pinch\Component\App\App;
use PhoneBurner\Pinch\Component\App\ServiceContainer;
use PhoneBurner\Pinch\Component\App\ServiceContainerFactory;
use PhoneBurner\Pinch\Component\Configuration\Configuration;
use PhoneBurner\Pinch\Component\Configuration\ConfigurationFactory;
use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\Environment;
use PhoneBurner\Pinch\Component\Configuration\ImmutableConfiguration;
use PhoneBurner\Pinch\Container\ParameterOverride\OverrideCollection;

use const PhoneBurner\Pinch\UNIT_TEST_ROOT;

class MockApp implements App
{
    public function __construct(
        public ServiceContainer $services = new MockContainer(),
        public Context $context = Context::Test,
        public Environment $environment = new MockEnvironment(UNIT_TEST_ROOT),
        public Configuration $config = new ImmutableConfiguration([]),
    ) {
    }

    public function has(\Stringable|string $id, bool $strict = false): bool
    {
        return $this->services->has($id);
    }

    /**
     * @template T of object
     * @return ($id is class-string<T> ? T : never)
     * @phpstan-assert class-string<T> $id
     */
    public function get(\Stringable|string $id): object
    {
        $value = $this->services->get($id);
        /** @var T $value */
        return $value;
    }

    public function set(\Stringable|string $id, mixed $value): void
    {
        $this->services->set($id, $value);
    }

    public function unset(\Stringable|string $id): void
    {
        $this->services->unset($id);
    }

    public function call(
        object|string $object,
        string $method = '__invoke',
        OverrideCollection|null $overrides = null,
    ): mixed {
        return $this->services->call($object, $method, $overrides);
    }

    public function __get(string $name): mixed
    {
        if ($name === 'services') {
            return $this->services;
        }
        throw new \RuntimeException(\sprintf('Property %s not found', $name));
    }

    public static function booted(): bool
    {
        return true;
    }

    public static function instance(): App
    {
        throw new \RuntimeException('Not implemented');
    }

    public static function bootstrap(
        Environment $environment,
        ConfigurationFactory|Configuration|null $config = null,
        ServiceContainerFactory|ServiceContainer|null $services = null,
    ): App {
        throw new \RuntimeException('Not implemented');
    }

    public static function teardown(): null
    {
        return null;
    }

    public static function exec(Environment $environment, callable $callback): mixed
    {
        throw new \RuntimeException('Not implemented');
    }
}
