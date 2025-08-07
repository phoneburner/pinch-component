<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response;

use Laminas\Diactoros\Response;
use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Stream\IteratorStream;
use PhoneBurner\Pinch\Time\TimeInterval\TimeInterval;

use const PhoneBurner\Pinch\Time\SECONDS_IN_MINUTE;

class ServerSentEventsResponse extends Response
{
    public const array DEFAULT_HEADERS = [
        HttpHeader::X_ACCEL_BUFFERING => 'no',
        HttpHeader::CONTENT_TYPE => ContentType::EVENT_STREAM,
        HttpHeader::CACHE_CONTROL => 'no-cache',
        HttpHeader::CONNECTION => 'keep-alive',
    ];

    public function __construct(
        iterable $iterator,
        public TimeInterval $ttl = new TimeInterval(seconds: 10 * SECONDS_IN_MINUTE),
        array $headers = self::DEFAULT_HEADERS,
    ) {
        parent::__construct(new IteratorStream($iterator), HttpStatus::OK, $headers);
    }
}
