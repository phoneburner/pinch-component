<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Cryptography\KeyManagement;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\EncryptionKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\Cryptography\KeyManagement\KeyChain;
use PhoneBurner\Pinch\Component\Cryptography\Symmetric\SharedKey;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class KeyChainTest extends TestCase
{
    private SharedKey $app_key;
    private KeyChain $key_chain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app_key = SharedKey::generate();
        $this->key_chain = new KeyChain($this->app_key);
    }

    #[Test]
    public function constructorStoresAppKey(): void
    {
        self::assertSame($this->app_key, $this->key_chain->app_key);
    }

    #[Test]
    public function sharedWithoutContextReturnsAppKey(): void
    {
        self::assertSame($this->app_key, $this->key_chain->shared());
    }

    #[Test]
    public function sharedWithContextDerivesAndCachesKey(): void
    {
        $context = 'testing-context';
        $derived_key1 = $this->key_chain->shared($context);
        $derived_key2 = $this->key_chain->shared($context);

        self::assertInstanceOf(SharedKey::class, $derived_key1);
        self::assertNotSame($this->app_key, $derived_key1);
        self::assertSame($derived_key1, $derived_key2, 'Derived key should be cached');
    }

    #[Test]
    public function sharedWithDifferentContextsDerivesDifferentKeys(): void
    {
        $context1 = 'context-1';
        $context2 = 'context-2';

        $derived_key1 = $this->key_chain->shared($context1);
        $derived_key2 = $this->key_chain->shared($context2);

        self::assertNotSame($derived_key1, $derived_key2);
        self::assertNotSame($derived_key1->bytes(), $derived_key2->bytes());
    }

    #[Test]
    public function encryptionDerivesAndCachesKeyPair(): void
    {
        $key_pair1 = $this->key_chain->encryption();
        $key_pair2 = $this->key_chain->encryption();

        self::assertInstanceOf(EncryptionKeyPair::class, $key_pair1);
        self::assertSame($key_pair1, $key_pair2, 'Encryption key pair should be cached');
    }

    #[Test]
    public function signatureDerivesAndCachesKeyPair(): void
    {
        $key_pair1 = $this->key_chain->signature();
        $key_pair2 = $this->key_chain->signature();

        self::assertInstanceOf(SignatureKeyPair::class, $key_pair1);
        self::assertSame($key_pair1, $key_pair2, 'Signature key pair should be cached');
    }

    #[Test]
    public function clearRemovesCachedKeysAndKeyPairs(): void
    {
        $context = 'shared-context';
        $shared_key1 = $this->key_chain->shared($context);
        $encryption_key_pair1 = $this->key_chain->encryption();
        $signature_key_pair1 = $this->key_chain->signature();

        // Ensure keys are cached (by checking count, though we tested caching above)
        self::assertCount(1, $this->key_chain);

        $this->key_chain->clear();

        // Check inherited collection is cleared
        self::assertCount(0, $this->key_chain);
        self::assertTrue($this->key_chain->isEmpty());

        // Check derived keys are re-derived after clear
        $shared_key2 = $this->key_chain->shared($context);
        $encryption_key_pair2 = $this->key_chain->encryption();
        $signature_key_pair2 = $this->key_chain->signature();

        self::assertNotSame($shared_key1, $shared_key2);
        self::assertNotSame($encryption_key_pair1, $encryption_key_pair2);
        self::assertNotSame($signature_key_pair1, $signature_key_pair2);
    }
}
