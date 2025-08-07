<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Paseto\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoCryptoException;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoVersion;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol\Version3;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Filesystem\File;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PhoneBurner\Pinch\String\Encoding\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Type\narrow_iterable;

final class Version3Test extends TestCase
{
    public const PasetoVersion VERSION = PasetoVersion::V3;

    public const string TEST_VECTOR_FILE = __DIR__ . '/../../Fixtures/paseto_test_vectors_v3.json';

    #[Test]
    #[DataProvider('provideTestVectorsEncryptPass')]
    public function pasetoStandardTestVectorsEncryptPass(array $test_vector): void
    {
        $key = SharedKey::import($test_vector['key'], Encoding::Hex);

        $this->expectException(PasetoCryptoException::class);
        $this->expectExceptionMessage('Unsupported Paseto Protocol Version');
        Version3::decrypt($key, new Paseto($test_vector['token']), $test_vector['implicit-assertion']);
    }

    #[Test]
    #[DataProvider('provideTestVectorsSignPass')]
    public function pasetoStandardTestVectorsSignPass(array $test_vector): void
    {
        $public_key = SignatureKeyPair::generate()->public;
        $this->expectException(PasetoCryptoException::class);
        $this->expectExceptionMessage('Unsupported Paseto Protocol Version');
        Version3::verify($public_key, new Paseto($test_vector['token']), $test_vector['implicit-assertion']);
    }

    #[Test]
    #[DataProvider('provideTestVectorsEncryptFail')]
    public function pasetoStandardTestVectorsEncryptFail(array $test_vector): void
    {
        $key = SharedKey::import($test_vector['key'], Encoding::Hex);

        $this->expectException(PasetoCryptoException::class);
        Version3::decrypt($key, new Paseto($test_vector['token']), $test_vector['implicit-assertion']);
    }

    #[Test]
    #[DataProvider('provideTestVectorsSignFail')]
    public function pasetoStandardTestVectorsSignFail(array $test_vector): void
    {
        $public_key = SignatureKeyPair::generate()->public;
        $this->expectException(PasetoCryptoException::class);
        Version3::verify($public_key, new Paseto($test_vector['token']), $test_vector['implicit-assertion']);
    }

    public static function provideTestVectorsEncryptPass(): \Generator
    {
        $test_vectors = Json::decode(File::read(self::TEST_VECTOR_FILE));
        foreach (narrow_iterable($test_vectors['tests']) as $test_vector) {
            self::assertIsArray($test_vector);
            self::assertIsString($test_vector['name']);
            $name = $test_vector['name'];
            if ($name[2] === 'E' && $test_vector['expect-fail'] === false) {
                yield $name => [$test_vector];
            }
        }
    }

    /**
     * @return \Generator<string, array{0: array<string,mixed>}>
     */
    public static function provideTestVectorsEncryptFail(): \Generator
    {
        $test_vectors = Json::decode(File::read(self::TEST_VECTOR_FILE));
        foreach (narrow_iterable($test_vectors['tests']) as $test_vector) {
            self::assertIsArray($test_vector);
            self::assertIsString($test_vector['name']);
            $name = $test_vector['name'];
            if ($name[2] === 'F' && $test_vector['expect-fail'] === true && isset($test_vector['key'])) {
                yield $name => [$test_vector];
            }
        }
    }

    /**
     * @return \Generator<string, array{0: array<string,mixed>}>
     */
    public static function provideTestVectorsSignPass(): \Generator
    {
        $test_vectors = Json::decode(File::read(self::TEST_VECTOR_FILE));
        foreach (narrow_iterable($test_vectors['tests']) as $test_vector) {
            self::assertIsArray($test_vector);
            self::assertIsString($test_vector['name']);
            $name = $test_vector['name'];
            if ($name[2] === 'S' && $test_vector['expect-fail'] === false) {
                yield $name => [$test_vector];
            }
        }
    }

    /**
     * @return \Generator<string, array{0: array<string,mixed>}>
     */
    public static function provideTestVectorsSignFail(): \Generator
    {
        $test_vectors = Json::decode(File::read(self::TEST_VECTOR_FILE));
        foreach (narrow_iterable($test_vectors['tests']) as $test_vector) {
            self::assertIsArray($test_vector);
            self::assertIsString($test_vector['name']);
            $name = $test_vector['name'];
            if ($name[2] === 'F' && $test_vector['expect-fail'] === true && isset($test_vector['secret-key'])) {
                yield $name => [$test_vector];
            }
        }
    }
}
