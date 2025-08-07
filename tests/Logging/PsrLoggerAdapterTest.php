<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Logging;

use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PhoneBurner\Pinch\Component\Logging\PsrLoggerAdapter;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockLogger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel as Psr3LogLevel;

use function PhoneBurner\Pinch\Array\array_wrap;

final class PsrLoggerAdapterTest extends TestCase
{
    private MockLogger $test_logger;
    private PsrLoggerAdapter $adapter;

    protected function setUp(): void
    {
        $this->test_logger = new MockLogger();
        $this->adapter = new PsrLoggerAdapter($this->test_logger);
    }

    #[Test]
    public function itDelegatesLogCallsToInnerLogger(): void
    {
        $this->adapter->log('info', 'Test message 1', ['key' => 'value']);
        $this->adapter->log('error', 'Test message 2');
        $this->adapter->log('debug', 'Test message 3', ['data' => ['nested' => true]]);

        $logs = $this->test_logger->getLogs();
        self::assertCount(3, $logs);

        self::assertSame('info', $logs[0]['level']);
        self::assertSame('Test message 1', $logs[0]['message']);
        self::assertSame(['key' => 'value'], $logs[0]['context']);

        self::assertSame('error', $logs[1]['level']);
        self::assertSame('Test message 2', $logs[1]['message']);
        self::assertSame([], $logs[1]['context']);

        self::assertSame('debug', $logs[2]['level']);
        self::assertSame('Test message 3', $logs[2]['message']);
        self::assertSame(['data' => ['nested' => true]], $logs[2]['context']);
    }

    #[Test]
    public function itDelegatesLogLevelEnumToInnerLogger(): void
    {
        $this->adapter->log(LogLevel::Warning, 'Test with enum', ['context' => true]);

        $logs = $this->test_logger->getLogs();
        self::assertCount(1, $logs);
        self::assertSame('warning', $logs[0]['level']);
        self::assertSame('Test with enum', $logs[0]['message']);
        self::assertSame(['context' => true], $logs[0]['context']);
    }

    #[Test]
    #[DataProvider('providesLevelValues')]
    public function itCanOverrideTheLogLevel(LogLevel|string $level): void
    {
        foreach (LogLevel::cases() as $log_level_override) {
            $test_logger = new MockLogger();
            $adapter = new PsrLoggerAdapter($test_logger, $log_level_override);
            $adapter->log($level, 'Test message 1', ['data' => ['nested' => true]]);

            $logs = $test_logger->getLogs();
            self::assertCount(1, $logs);
            self::assertSame($log_level_override->value, $logs[0]['level']);
            self::assertSame('Test message 1', $logs[0]['message']);
            self::assertSame(['data' => ['nested' => true]], $logs[0]['context']);
        }
    }

    public static function providesLevelValues(): \Generator
    {
        yield from \array_map(array_wrap(...), LogLevel::cases());
        yield from [
            [Psr3LogLevel::EMERGENCY],
            [Psr3LogLevel::ALERT],
            [Psr3LogLevel::CRITICAL],
            [Psr3LogLevel::ERROR],
            [Psr3LogLevel::WARNING],
            [Psr3LogLevel::NOTICE],
            [Psr3LogLevel::INFO],
            [Psr3LogLevel::DEBUG],
        ];
    }

    #[Test]
    public function itCanPrefixTheMessage(): void
    {
        $this->adapter = new PsrLoggerAdapter($this->test_logger, null, 'Prefix: ');
        $this->adapter->log('info', 'Test message 1', ['key' => 'value']);

        $logs = $this->test_logger->getLogs();
        self::assertCount(1, $logs);
        self::assertSame('info', $logs[0]['level']);
        self::assertSame('Prefix: Test message 1', $logs[0]['message']);
        self::assertSame(['key' => 'value'], $logs[0]['context']);
    }

    #[Test]
    public function itCanOverrideContext(): void
    {
        $this->adapter = new PsrLoggerAdapter($this->test_logger, null, '', ['override' => 'value']);
        $this->adapter->log('info', 'Test message 1', ['key' => 'value']);
        $this->adapter->log('info', 'Test message 2', ['key' => 'value', 'override' => 'other']);

        $logs = $this->test_logger->getLogs();
        self::assertCount(2, $logs);
        self::assertSame('info', $logs[0]['level']);
        self::assertSame('Test message 1', $logs[0]['message']);
        self::assertSame(['key' => 'value', 'override' => 'value'], $logs[0]['context']);
        self::assertSame('info', $logs[1]['level']);
        self::assertSame('Test message 2', $logs[1]['message']);
        self::assertSame(['key' => 'value', 'override' => 'value'], $logs[1]['context']);
    }

    public function itCanResetInnerLogger(): void
    {
        $this->adapter->log('info', 'Test message 1');
        $this->adapter->log('error', 'Test message 2');
        self::assertCount(2, $this->test_logger->getLogs());

        $this->adapter->reset();
        self::assertCount(0, $this->test_logger->getLogs());
    }

    #[Test]
    public function itAcceptsStringableMessage(): void
    {
        $stringable = new class () implements \Stringable {
            public function __toString(): string
            {
                return 'Stringable message';
            }
        };

        $this->adapter->log('notice', $stringable);

        $logs = $this->test_logger->getLogs();
        self::assertCount(1, $logs);
        self::assertSame('notice', $logs[0]['level']);
        self::assertSame($stringable, $logs[0]['message']);
        self::assertSame([], $logs[0]['context']);
    }

    #[Test]
    public function itProcessesLogEntryObjects(): void
    {
        $entry = new LogEntry(LogLevel::Critical, 'Critical error', ['error' => 'details']);

        $this->adapter->add($entry);

        $logs = $this->test_logger->getLogs();
        self::assertCount(1, $logs);
        self::assertSame(LogLevel::Critical, $logs[0]['level']);
        self::assertSame('Critical error', $logs[0]['message']);
        self::assertSame(['error' => 'details'], $logs[0]['context']);
    }

    #[Test]
    public function itProcessesLoggableObjects(): void
    {
        $loggable = new class () implements Loggable {
            public function getLogEntry(): LogEntry
            {
                return new LogEntry(LogLevel::Alert, 'Alert from loggable', ['source' => 'test']);
            }
        };

        $this->adapter->add($loggable);

        $logs = $this->test_logger->getLogs();
        self::assertCount(1, $logs);
        self::assertSame(LogLevel::Alert, $logs[0]['level']);
        self::assertSame('Alert from loggable', $logs[0]['message']);
        self::assertSame(['source' => 'test'], $logs[0]['context']);
    }
}
