<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Paseto;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPairSeed;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignaturePublicKey;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureSecretKey;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\Paserk;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PaserkType;
use PhoneBurner\Pinch\Component\Cryptography\Paseto\PaserkVersion;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PhoneBurner\Pinch\Component\Tests\Cryptography\Fixtures\PaserkTestVectorStruct;
use PhoneBurner\Pinch\String\Encoding\Encoding;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaserkTest extends TestCase
{
    public const array KEY_TEST_VECTORS = [
        [
            'key' => '0000000000000000000000000000000000000000000000000000000000000000',
            'secret' => 'k4.secret.AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA7aie8zrakLWKjqNAqbw1zZTIVdx3iQ6Y6wEihi1naKQ',
            'sid' => 'k4.sid.YujQ-NvcGquQ0Q-arRf8iYEcXiSOKg2Vk5az-n1lxiUd',
            'public' => 'k4.public.AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
            'pid' => 'k4.pid.S_XQmeEwHbbvRmiyfXfHYpLGjXGzjTRSDoT1YtTakWFE',
            'local' => 'k4.local.AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
            'lid' => 'k4.lid.bqltbNc4JLUAmc9Xtpok-fBuI0dQN5_m3CD9W_nbh559',
        ],
        [
            'key' => '707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e8f',
            'secret' => 'k4.secret.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjo8c5WpIyC_5kWKhS8VEYSZ05dYfuTF-ZdQFV4D9vLTcNQ',
            'sid' => 'k4.sid.gHYyx8y5YzqKEZeYoMDqUOKejdSnY_AWhYZiSCMjR1V5',
            'public' => 'k4.public.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjo8',
            'pid' => 'k4.pid.9ShR3xc8-qVJ_di0tc9nx0IDIqbatdeM2mqLFBJsKRHs',
            'local' => 'k4.local.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjo8',
            'lid' => 'k4.lid.iVtYQDjr5gEijCSjJC3fQaJm7nCeQSeaty0Jixy8dbsk',
        ],
        [
            'key' => '707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e90',
            'secret' => 'k4.secret.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjpBg_jdXGl1ufTCxUVTOSp-5LHDIcISPTM3xYmWICX9z9w',
            'sid' => 'k4.sid.2_m4h6ZTO3qm_PIpl-eYyAqTbNTgmIPQ85POmUEyZHNd',
            'public' => 'k4.public.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjpA',
            'pid' => 'k4.pid.-nyvbaTz8U6TQz7OZWW-iB3va31iAxIpUgzUcVQVmW9A',
            'local' => 'k4.local.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjpA',
            'lid' => 'k4.lid.-v0wjDR1FVxNT2to41Ay1P4_8X6HIxnybX1nZ1a4FCTm',
        ],
    ];

    public const array KEYPAIR_TEST_VECTORS = [
        [
            'key' => '707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e9060fe37571a5d6e7d30b15154ce4a9fb92c70c870848f4ccdf1626588097f73f7',
            'secret' => 'k4.secret.cHFyc3R1dnd4eXp7fH1-f4CBgoOEhYaHiImKi4yNjpBg_jdXGl1ufTCxUVTOSp-5LHDIcISPTM3xYmWICX9z9w',
            'sid' => 'k4.sid.2_m4h6ZTO3qm_PIpl-eYyAqTbNTgmIPQ85POmUEyZHNd',
            'public' => 'k4.public.YP43Vxpdbn0wsVFUzkqfuSxwyHCEj0zN8WJliAl_c_c',
            'pid' => 'k4.pid.935bxbO7t_1a48JzJTePcXWiFmc791-cNw4pot2V59Oo',
        ],
        [
            'key' => '4b04e7c418200dc839212ce64ff6c1cb9a4dd383f0053fc482b7381f1e66b3c04940f99a9b3abdf02f3ac50c92f03222b5266461eab716bfdd36da860fbfa381',
            'secret' => 'k4.secret.SwTnxBggDcg5ISzmT_bBy5pN04PwBT_Egrc4Hx5ms8BJQPmamzq98C86xQyS8DIitSZkYeq3Fr_dNtqGD7-jgQ',
            'sid' => 'k4.sid._8MRRDpmo0KGg8FPnqqjD1HIVZQJeO6wAkN4vD8W94eY',
            'public' => 'k4.public.SUD5mps6vfAvOsUMkvAyIrUmZGHqtxa_3Tbahg-_o4E',
            'pid' => 'k4.pid.ZnpQBqt-8e6B83UG4gas3w8sVV9efnzMWwg_lGin4Wd4',
        ],
    ];

    #[Test]
    #[DataProvider('providesNamedConstructorTests')]
    public function namedConstructorTest(PaserkTestVectorStruct $vector): void
    {
        $keypair = SignatureKeyPair::fromSeed(
            SignatureKeyPairSeed::import($vector->key, Encoding::Hex),
        );

        $secret_paserk = Paserk::secret($keypair);
        self::assertSame($vector->version, $secret_paserk->version);
        self::assertSame(PaserkType::Secret, $secret_paserk->type);
        self::assertSame($vector->secret, (string)$secret_paserk);
        self::assertSame($vector->secret, (string)Paserk::secret($keypair->secret));
        self::assertSame($vector->secret, (string)Paserk::import($vector->secret));
        self::assertSame($vector->secret, (string)Paserk::tryImport($vector->secret));

        $sid_paserk = Paserk::sid($keypair);
        self::assertSame($vector->version, $sid_paserk->version);
        self::assertSame(PaserkType::SecretId, $sid_paserk->type);
        self::assertSame($vector->sid, (string)$sid_paserk);
        self::assertSame($vector->sid, (string)Paserk::sid($keypair->secret));
        self::assertSame($vector->sid, (string)Paserk::sid($secret_paserk));
        self::assertSame($vector->sid, (string)Paserk::import($vector->sid));
        self::assertSame($vector->sid, (string)Paserk::tryImport($vector->sid));

        $public_paserk = Paserk::public(SignaturePublicKey::import($vector->key, Encoding::Hex));
        self::assertSame($vector->version, $public_paserk->version);
        self::assertSame(PaserkType::Public, $public_paserk->type);
        self::assertSame($vector->public, (string)$public_paserk);
        self::assertSame($vector->public, (string)Paserk::import($vector->public));
        self::assertSame($vector->public, (string)Paserk::tryImport($vector->public));

        $pid_paserk = Paserk::pid(SignaturePublicKey::import($vector->key, Encoding::Hex));
        self::assertSame($vector->version, $pid_paserk->version);
        self::assertSame(PaserkType::PublicId, $pid_paserk->type);
        self::assertSame($vector->pid, (string)$pid_paserk);
        self::assertSame($vector->pid, (string)Paserk::pid($public_paserk));
        self::assertSame($vector->pid, (string)Paserk::import($vector->pid));
        self::assertSame($vector->pid, (string)Paserk::tryImport($vector->pid));

        $local_paserk = Paserk::local(SharedKey::import($vector->key, Encoding::Hex));
        self::assertSame($vector->version, $local_paserk->version);
        self::assertSame(PaserkType::Local, $local_paserk->type);
        self::assertSame($vector->local, (string)$local_paserk);
        self::assertSame($vector->local, (string)Paserk::import($vector->local));
        self::assertSame($vector->local, (string)Paserk::tryImport($vector->local));

        $lid_paserk = Paserk::lid(SharedKey::import($vector->key, Encoding::Hex));
        self::assertSame($vector->version, $lid_paserk->version);
        self::assertSame(PaserkType::LocalId, $lid_paserk->type);
        self::assertSame($vector->lid, (string)$lid_paserk);
        self::assertSame($vector->lid, (string)Paserk::lid($local_paserk));
        self::assertSame($vector->lid, (string)Paserk::import($vector->lid));
        self::assertSame($vector->lid, (string)Paserk::tryImport($vector->lid));
    }

    #[Test]
    #[DataProvider('providesKeyPairTests')]
    public function keypairsReturnExpectedValues(PaserkTestVectorStruct $vector): void
    {
        $keypair = SignatureKeyPair::fromSecretKey(
            SignatureSecretKey::import($vector->key, Encoding::Hex),
        );

        self::assertSame($vector->secret, (string)Paserk::secret($keypair));
        self::assertSame($vector->secret, (string)Paserk::secret($keypair->secret));
        self::assertSame($vector->sid, (string)Paserk::sid($keypair));
        self::assertSame($vector->sid, (string)Paserk::sid($keypair->secret));
        self::assertSame($vector->public, (string)Paserk::public($keypair));
        self::assertSame($vector->public, (string)Paserk::public($keypair->public));
        self::assertSame($vector->pid, (string)Paserk::pid($keypair));
        self::assertSame($vector->pid, (string)Paserk::pid($keypair->public));
    }

    public static function providesKeyPairTests(): \Generator
    {
        foreach (self::KEYPAIR_TEST_VECTORS as $test_vector) {
            yield [new PaserkTestVectorStruct(
                PaserkVersion::V4,
                $test_vector['key'],
                $test_vector['secret'],
                $test_vector['sid'],
                $test_vector['public'],
                $test_vector['pid'],
                '',
                '',
            )];
        }
    }

    public static function providesNamedConstructorTests(): \Generator
    {
        foreach (self::KEY_TEST_VECTORS as $test_vector) {
            yield [new PaserkTestVectorStruct(
                PaserkVersion::V4,
                $test_vector['key'],
                $test_vector['secret'],
                $test_vector['sid'],
                $test_vector['public'],
                $test_vector['pid'],
                $test_vector['local'],
                $test_vector['lid'],
            )];
        }
    }
}
