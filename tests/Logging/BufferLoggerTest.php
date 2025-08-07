<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Logging;

use PhoneBurner\Pinch\Component\Logging\BufferLogger;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stringable;

final class BufferLoggerTest extends TestCase
{
    private BufferLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new BufferLogger();
    }

    #[Test]
    public function logStoresMessageInBuffer(): void
    {
        $this->logger->log('info', 'test message', ['context' => 'value']);
        $entries = $this->logger->clear();

        self::assertCount(1, $entries);
        self::assertInstanceOf(LogEntry::class, $entries[0]);
        self::assertSame(LogLevel::Info, $entries[0]->level);
        self::assertSame('test message', $entries[0]->message);
        self::assertSame(['context' => 'value'], $entries[0]->context);
    }

    #[Test]
    public function logAcceptsStringableMessage(): void
    {
        $message = new class implements Stringable {
            public function __toString(): string
            {
                return 'stringable message';
            }
        };

        $this->logger->log('error', $message);
        $entries = $this->logger->clear();

        self::assertCount(1, $entries);
        self::assertSame($message, $entries[0]->message);
        self::assertSame(LogLevel::Error, $entries[0]->level);
    }

    #[Test]
    public function clearEmptiesBufferAndReturnsEntries(): void
    {
        $this->logger->info('first message');
        $this->logger->error('second message');

        $entries = $this->logger->clear();
        self::assertCount(2, $entries);

        self::assertCount(0, $this->logger->clear(), 'Buffer should be empty after clear');
    }

    #[Test]
    public function readWithoutMaxReturnsAllEntries(): void
    {
        $this->logger->info('first message');
        $this->logger->error('second message');

        $entries = $this->logger->read();
        self::assertCount(2, $entries);
        self::assertCount(0, $this->logger->read(), 'Buffer should be empty after read');
    }

    #[Test]
    public function readWithMaxReturnsLimitedEntries(): void
    {
        $this->logger->info('first message');
        $this->logger->error('second message');
        $this->logger->warning('third message');

        $entries = $this->logger->read(2);
        self::assertCount(2, $entries);
        self::assertSame('first message', $entries[0]->message);
        self::assertSame('second message', $entries[1]->message);

        $remaining = $this->logger->read();
        self::assertCount(1, $remaining);
        self::assertSame('third message', $remaining[0]->message);
    }

    #[Test]
    public function readEmptyBufferReturnsEmptyArray(): void
    {
        self::assertSame([], $this->logger->read());
    }

    #[Test]
    public function writeAddsEntriesToBuffer(): void
    {
        $entry1 = new LogEntry(LogLevel::Info, 'first message', []);
        $entry2 = new LogEntry(LogLevel::Error, 'second message', ['context' => 'value']);

        $this->logger->write($entry1, $entry2);

        $entries = $this->logger->clear();
        self::assertCount(2, $entries);
        self::assertSame($entry1, $entries[0]);
        self::assertSame($entry2, $entries[1]);
    }

    #[Test]
    public function countReturnsNumberOfEntries(): void
    {
        self::assertCount(0, $this->logger);

        $this->logger->info('first message');
        self::assertCount(1, $this->logger);

        $this->logger->error('second message');
        self::assertCount(2, $this->logger);

        $this->logger->clear();
        self::assertCount(0, $this->logger);
    }

    #[Test]
    public function copyTransfersEntriesToAnotherLogger(): void
    {
        $targetLogger = self::createMock(LoggerInterface::class);
        $targetLogger->expects(self::exactly(2))
            ->method('log')
            ->willReturnCallback(function (string $level, string $message, array $context): void {
                static $calls = 0;
                ++$calls;

                if ($calls === 1) {
                    self::assertSame('info', $level);
                    self::assertSame('first message', $message);
                    self::assertSame([], $context);
                } elseif ($calls === 2) {
                    self::assertSame('error', $level);
                    self::assertSame('second message', $message);
                    self::assertSame(['context' => 'value'], $context);
                } else {
                    self::fail('Unexpected call');
                }
            });

        $this->logger->info('first message');
        $this->logger->error('second message', ['context' => 'value']);

        $this->logger->copy($targetLogger);
        self::assertCount(2, $this->logger, 'Original buffer should remain unchanged after copy');
    }

    #[Test]
    public function loggerTraitMethodsWork(): void
    {
        $this->logger->emergency('emergency message', ['context' => 1]);
        $this->logger->alert('alert message', ['context' => 2]);
        $this->logger->critical('critical message', ['context' => 3]);
        $this->logger->error('error message', ['context' => 4]);
        $this->logger->warning('warning message', ['context' => 5]);
        $this->logger->notice('notice message', ['context' => 6]);
        $this->logger->info('info message', ['context' => 7]);
        $this->logger->debug('debug message', ['context' => 8]);

        $entries = $this->logger->clear();
        self::assertCount(8, $entries);

        $expectedLevels = [
            LogLevel::Emergency,
            LogLevel::Alert,
            LogLevel::Critical,
            LogLevel::Error,
            LogLevel::Warning,
            LogLevel::Notice,
            LogLevel::Info,
            LogLevel::Debug,
        ];

        foreach ($entries as $i => $entry) {
            self::assertSame($expectedLevels[$i], $entry->level);
            self::assertStringContainsString($entry->level->value, (string)$entry->message);
        }
    }
}
