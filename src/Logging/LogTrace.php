<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Logging;

use PhoneBurner\Pinch\Uuid\OrderedUuid;
use PhoneBurner\Pinch\Uuid\Uuid;
use PhoneBurner\Pinch\Uuid\UuidWrapper;
use Ramsey\Uuid\UuidInterface;

readonly final class LogTrace implements UuidInterface
{
    use UuidWrapper;

    public function __construct(public UuidInterface $uuid = new OrderedUuid())
    {
    }

    #[\Override]
    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * Create a log trace ID based on an RFC 4122 v7 UUID. This produces UUIDs
     * that are monotonically increasing and lexicographically sortable in both
     * hex and byte formats. This allows us to be able to compare logged entries
     * by when the request started, and not necessarily when the log entry was made.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Converts the instance to a string for PHP serialization, but as opposed
     * to how the `UUID` would normally serialize itself into a binary string,
     * we want to use the hex string version for maximum portability.
     *
     * @return array{uuid:non-empty-string}
     */
    #[\Override]
    public function __serialize(): array
    {
        return ['uuid' => $this->toString()];
    }

    /**
     * @param array{uuid:string} $data
     */
    #[\Override]
    public function __unserialize(array $data): void
    {
        $this->uuid = Uuid::instance($data['uuid']);
    }
}
