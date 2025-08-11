<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Cookie;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Exception\InvalidCookie;
use PhoneBurner\Pinch\Time\Clock\Clock;
use PhoneBurner\Pinch\Time\Clock\SystemClock;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;
use Psr\Http\Message\ResponseInterface;

use function PhoneBurner\Pinch\Math\int_clamp;

use const PhoneBurner\Pinch\Time\SECONDS_IN_DAY;

readonly class Cookie
{
    public const string RESERVED_CHARS_LIST = "=,; \t\r\n\v\f";
    public const array RESERVED_CHARS_FROM = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];
    public const array RESERVED_CHARS_TO = ['%3D', '%2C', '%3B', '%20', '%09', '%0D', '%0A', '%0B', '%0C'];

    public function __construct(
        public string $name,
        public \Stringable|string $value,
        public \DateTimeInterface|TimeInterval|null $ttl = null,
        public string $path = '/',
        public string $domain = '',
        public bool $secure = true,
        public bool $http_only = true,
        public SameSite|null $same_site = SameSite::Lax,
        public bool $partitioned = false,
        public bool $raw = false,
        public bool $encrypt = false,
    ) {
        if ($name === '') {
            throw new InvalidCookie('Cookie name cannot be empty');
        }

        if (\strpbrk($name, self::RESERVED_CHARS_LIST) !== false) {
            throw new InvalidCookie(\sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if ($this->same_site === SameSite::None && $this->secure === false) {
            throw new InvalidCookie('SameSite=None requires Secure Setting');
        }
    }

    public static function remove(
        string $name,
        string $path = '/',
        string $domain = '',
    ): self {
        return new self($name, '', null, $path, $domain);
    }

    public function withValue(\Stringable|string $value): self
    {
        return new self($this->name, $value, $this->ttl, $this->path, $this->domain, $this->secure, $this->http_only, $this->same_site, $this->partitioned, $this->raw);
    }

    public function value(): string
    {
        return (string)$this->value;
    }

    /**
     * This method added for convenience to set a one-off cookie on a response;
     * however, using the CookieManager to queue up and set cookies is preferred,
     * and safer, as it will handle encryption and decryption of cookies, as well
     * as preventing the loss of the cookie if a different response is returned
     * later in the middleware queue.
     */
    public function set(ResponseInterface $response, Clock $clock = new SystemClock()): ResponseInterface
    {
        return $response->withAddedHeader(HttpHeader::SET_COOKIE, $this->toString($clock));
    }

    public function toString(Clock $clock = new SystemClock()): string
    {
        $value = (string)$this->value;
        $name = $this->raw ? $this->name : \str_replace(self::RESERVED_CHARS_FROM, self::RESERVED_CHARS_TO, $this->name);
        return $name . '=' . \implode('; ', \array_filter([
            'value' => match (true) {
                $value === '' => 'deleted',
                $this->raw => $value,
                default => \rawurlencode($value),
            },
            'max-age' => match (true) {
                $value === '' => 'Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0',
                $this->ttl instanceof TimeInterval => \sprintf(
                    'Max-Age=%d',
                    \min($this->ttl->seconds, SECONDS_IN_DAY * 400),
                ),
                // Clamp the difference of the expires and current timestamps to between -1 and 400 days.
                $this->ttl instanceof \DateTimeInterface => \sprintf(
                    'Max-Age=%d',
                    int_clamp($this->ttl->getTimestamp() - $clock->now()->getTimestamp(), -1, SECONDS_IN_DAY * 400),
                ),
                $this->ttl === null => null,
            },
            'path' => $this->path ? \sprintf('Path=%s', $this->path) : null,
            'domain' => $this->domain ? \sprintf('Domain=%s', $this->domain) : null,
            'secure' => $this->secure ? 'Secure' : null,
            'http_only' => $this->http_only ? 'HttpOnly' : null,
            'same_site' => $this->same_site ? \sprintf('SameSite=%s', $this->same_site->name) : null,
            'partitioned' => $this->partitioned ? 'Partitioned' : null,
        ]));
    }
}
