<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Configuration\Struct;

use PhoneBurner\Pinch\Component\Tests\Fixtures\TestApiKeyConfigStruct;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiKeyConfigStructTest extends TestCase
{
    #[Test]
    public function constructorSetsApiKey(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        self::assertSame('test-api-key', $config->api_key);
    }

    #[Test]
    public function constructorSetsNullApiKey(): void
    {
        $config = new TestApiKeyConfigStruct(null);
        self::assertNull($config->api_key);
    }

    #[Test]
    public function constructorSetsEmptyStringToNull(): void
    {
        $config = new TestApiKeyConfigStruct('');
        self::assertNull($config->api_key);
    }

    #[Test]
    public function arrayAccessOffsetExists(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        self::assertArrayHasKey('api_key', $config);
        self::assertArrayNotHasKey('non_existent', $config);
    }

    #[Test]
    public function arrayAccessOffsetGet(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        self::assertSame('test-api-key', $config['api_key']);
    }

    #[Test]
    public function arrayAccessOffsetGetReturnsNullForNonExistentKey(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        self::assertNull($config['non_existent']);
    }

    #[Test]
    public function arrayAccessOffsetSetThrowsException(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Config Structs are Immutable');
        $config['api_key'] = 'new-key';
    }

    #[Test]
    public function arrayAccessOffsetUnsetThrowsException(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Config Structs are Immutable');
        unset($config['api_key']);
    }

    #[Test]
    public function serialization(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        $serialized = \serialize($config);
        $unserialized = \unserialize($serialized);
        self::assertEquals($config, $unserialized);
    }

    #[Test]
    public function jsonSerialization(): void
    {
        $config = new TestApiKeyConfigStruct('test-api-key');
        $json = \json_encode($config);
        self::assertSame('{"api_key":"test-api-key"}', $json);
    }
}
