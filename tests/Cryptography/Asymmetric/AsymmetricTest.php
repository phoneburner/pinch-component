<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\Asymmetric;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\Asymmetric;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\AsymmetricAlgorithm;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\String\MessageSignature;
use PhoneBurner\Pinch\Filesystem\File;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AsymmetricTest extends TestCase
{
    public const string KNOWN_SENDER_ENCRYPTION_KEYPAIR = 'kk72c6s2di5fKvBXLSbYCISOvj+a26p3nhe/+TzTi3osLpeqgv2ChN/RzsZskMYLU7jct02PprzdoHPeUwt5Kg==';

    public const string KNOWN_RECIPIENT_ENCRYPTION_KEYPAIR = 'fvVzvZ085EQ+chb5HtMzBhLcBHjVAQi1g4CnQfuJnjTGPBGm6sIenWqy7v7b4iNdaQhtpn6gDVtpXquKyo7KKQ==';

    public const string KNOWN_SIGNATURE_KEYPAIR = 'idOxepSuhF59BDvrimjszqDXrtdtBIgcLmTJRUQpbWHIvFyDdNItbTmkZW2fm2NSFQf-pLwzmSmX6G8Ot46VfMi8XIN00i1tOaRlbZ-bY1IVB_6kvDOZKZfobw63jpV8';

    public const string KNOWN_MESSAGE_SIGNATURE = 'hQr_LHoLyCc_d8RqB-gzybe0ayflIRckLYGagrck1wsjND-YTObh_-6yHs3H8wgh7WivJ0SO50KhHz2y7A2bBA';

    private string $known_plaintext;

    protected function setUp(): void
    {
        $this->known_plaintext = File::read(__DIR__ . '/../Fixtures/known_plaintext.txt');
    }

    #[Test]
    public function encryptionHappyPathXchacha20blake2b(): void
    {
        $sender_keypair = EncryptionKeyPair::generate();
        $recipient_keypair = EncryptionKeyPair::generate();

        $ciphertext = new Asymmetric()->encrypt(
            key_pair: $sender_keypair,
            public_key: $recipient_keypair->public,
            plaintext: $this->known_plaintext,
            algorithm: AsymmetricAlgorithm::X25519XChaCha20Blake2b,
        );
        $plaintext = new Asymmetric()->decrypt(
            key_pair: $recipient_keypair,
            public_key: $sender_keypair->public,
            ciphertext: $ciphertext,
            algorithm: AsymmetricAlgorithm::X25519XChaCha20Blake2b,
        );

        self::assertSame($this->known_plaintext, $plaintext);
    }

    #[Test]
    public function signAndVerifyHappyPath(): void
    {
        $key_pair = SignatureKeyPair::generate();

        $message_signature = new Asymmetric()->sign($key_pair, $this->known_plaintext);

        self::assertTrue(new Asymmetric()->verify($key_pair->public, $message_signature, $this->known_plaintext));
    }

    #[Test]
    public function signAndVerifyRegressionTest(): void
    {
        $key_pair = SignatureKeyPair::import(self::KNOWN_SIGNATURE_KEYPAIR);
        $message_signature = MessageSignature::import(self::KNOWN_MESSAGE_SIGNATURE);

        self::assertTrue(new Asymmetric()->verify($key_pair->public, $message_signature, $this->known_plaintext));
    }
}
