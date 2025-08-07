<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Logging;

use PhoneBurner\Pinch\Container\ResettableService;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Wrap a PSR-3 LoggerInterface as a PSR-3 LoggerInterface, allowing us to use
 * our own LogLevel enum, as well as override the log level for any messages logged
 * by this adapter instance. An optional prefix can be added to the message string,
 * and values in the context array can be overridden by the key. The override is
 * merged into the passed context array, so it will overwrite the value if it
 * exists. That would be useful if wrapping a third-party logger that adds something
 * into the context like an API token that we don't want to persist in the log.
 */
class PsrLoggerAdapter extends AbstractLogger implements ResettableService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LogLevel|null $level_override = null,
        private readonly string $message_prefix = '',
        private readonly array $context_override = [],
    ) {
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $level = match (true) {
            $this->level_override !== null => $this->level_override->value,
            $level instanceof LogLevel => $level->value,
            default => $level,
        };

        if ($this->message_prefix !== '') {
            $message = $this->message_prefix . $message;
        }

        if ($this->context_override !== []) {
            $context = \array_merge($context, $this->context_override);
        }

        $this->logger->log($level, $message, $context);
    }

    public function add(LogEntry|Loggable $entry): void
    {
        if ($entry instanceof Loggable) {
            $entry = $entry->getLogEntry();
        }

        $this->logger->log($entry->level, $entry->message, $entry->context);
    }

    public function reset(): void
    {
        if (\method_exists($this->logger, 'reset')) {
            $this->logger->reset();
        }
    }
}
