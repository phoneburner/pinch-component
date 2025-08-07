<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Paseto\Protocol;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPairSeed;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureSecretKey;
use PhoneBurner\Pinch\Component\Cryptography\Exception\PasetoCryptoException;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Claims\PasetoMessage;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paseto;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PasetoVersion;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Protocol\Version4;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Filesystem\File;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PhoneBurner\Pinch\String\Encoding\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Type\narrow_iterable;

final class Version4Test extends TestCase
{
    public const PasetoVersion VERSION = PasetoVersion::V4;

    public const string TEST_VECTOR_FILE = __DIR__ . '/../../Fixtures/paseto_test_vectors_v4.json';

    #[Test]
    #[DataProvider('provideTestVectorsEncryptPass')]
    public function pasetoStandardTestVectorsEncryptPass(array $test_vector): void
    {
        $key = SharedKey::import($test_vector['key'], Encoding::Hex);
        $vector_token = new Paseto($test_vector['token']);

        $message = Version4::decrypt($key, $vector_token, $test_vector['implicit-assertion']);

        self::assertSame($test_vector['payload'], $message->payload);
        self::assertSame($test_vector['footer'], $message->footer);

        $token = Version4::encrypt($key, $message, $test_vector['implicit-assertion']);
        self::assertNotEquals($vector_token, $token);

        $message = Version4::decrypt($key, $token, $test_vector['implicit-assertion']);

        self::assertSame($test_vector['payload'], $message->payload);
        self::assertSame($test_vector['footer'], $message->footer);
    }

    #[Test]
    #[DataProvider('provideTestVectorsSignPass')]
    public function pasetoStandardTestVectorsSignPass(array $test_vector): void
    {
        $secret_key = SignatureSecretKey::import($test_vector['secret-key'], Encoding::Hex);
        $public_key = SignaturePublicKey::import($test_vector['public-key'], Encoding::Hex);

        $key_pair_seed = SignatureKeyPairSeed::import($test_vector['secret-key-seed'], Encoding::Hex);
        $key_pair = SignatureKeyPair::fromSeed($key_pair_seed);

        self::assertEquals($secret_key, $key_pair->secret);
        self::assertEquals($public_key, $key_pair->public);

        $vector_token = new Paseto($test_vector['token']);

        $message = Version4::verify($public_key, $vector_token, $test_vector['implicit-assertion']);

        self::assertSame($test_vector['payload'], $message->payload);
        self::assertSame($test_vector['footer'], $message->footer);

        $token = Version4::sign($key_pair, new PasetoMessage(
            $test_vector['payload'],
            $test_vector['footer'],
        ), $test_vector['implicit-assertion']);

        self::assertEquals($vector_token, $token);

        $message = Version4::verify($key_pair->public, $token, $test_vector['implicit-assertion']);

        self::assertSame($test_vector['payload'], $message->payload);
        self::assertSame($test_vector['footer'], $message->footer);
    }

    #[Test]
    #[DataProvider('provideTestVectorsEncryptFail')]
    public function pasetoStandardTestVectorsEncryptFail(array $test_vector): void
    {
        $key = SharedKey::import($test_vector['key'], Encoding::Hex);

        $this->expectException(PasetoCryptoException::class);
        Version4::decrypt($key, new Paseto($test_vector['token']), $test_vector['implicit-assertion']);
    }

    #[Test]
    #[DataProvider('provideTestVectorsSignFail')]
    public function pasetoStandardTestVectorsSignFail(array $test_vector): void
    {
        $secret_key = SignatureSecretKey::import($test_vector['secret-key'], Encoding::Hex);
        $public_key = SignaturePublicKey::import($test_vector['public-key'], Encoding::Hex);

        $key_pair_seed = SignatureKeyPairSeed::import($test_vector['secret-key-seed'], Encoding::Hex);
        $key_pair = SignatureKeyPair::fromSeed($key_pair_seed);

        self::assertEquals($secret_key, $key_pair->secret);
        self::assertEquals($public_key, $key_pair->public);

        // Inline the instantiation of the token because we use the self-valid
        // nature of the value object to enforce algorithm lucidity at the engine level
        $this->expectException(PasetoCryptoException::class);
        Version4::verify($public_key, new Paseto($test_vector['token']), $test_vector['implicit-assertion']);
    }

    /**
     * @return \Generator<string, array{0: array<string,mixed>}>
     */
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
