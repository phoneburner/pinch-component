<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Cookie;

use Laminas\Diactoros\Response;
use PhoneBurner\Pinch\Component\Http\Cookie\Cookie;
use PhoneBurner\Pinch\Component\Http\Cookie\SameSite;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Exception\InvalidCookie;
use PhoneBurner\Pinch\Time\Interval\TimeInterval;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CookieTest extends TestCase
{
    #[Test]
    public function constructorValidatesEmptyName(): void
    {
        $this->expectException(InvalidCookie::class);
        $this->expectExceptionMessage('Cookie name cannot be empty');
        new Cookie('', 'value');
    }

    #[Test]
    public function constructorValidatesNameWithReservedChars(): void
    {
        $this->expectException(InvalidCookie::class);
        $this->expectExceptionMessage('The cookie name "invalid=name" contains invalid characters.');
        new Cookie('invalid=name', 'value');
    }

    #[Test]
    public function constructorValidatesSameSiteNoneRequiresSecure(): void
    {
        $this->expectException(InvalidCookie::class);
        $this->expectExceptionMessage('SameSite=None requires Secure Setting');
        new Cookie('test', 'value', null, '/', '', false, true, SameSite::None);
    }

    #[Test]
    public function removalCookieHasEmptyValue(): void
    {
        $cookie = Cookie::remove('test_cookie', '/path', 'example.com');

        self::assertSame('test_cookie', $cookie->name);
        self::assertSame('', $cookie->value());
        self::assertSame('/path', $cookie->path);
        self::assertSame('example.com', $cookie->domain);
    }

    #[Test]
    public function withValueReturnsNewInstanceWithUpdatedValue(): void
    {
        $cookie = new Cookie('test', 'original');
        $updated_cookie = $cookie->withValue('new_value');

        self::assertSame('original', $cookie->value());
        self::assertSame('new_value', $updated_cookie->value());
        self::assertSame($cookie->name, $updated_cookie->name);
        self::assertSame($cookie->path, $updated_cookie->path);
        self::assertSame($cookie->domain, $updated_cookie->domain);
        self::assertSame($cookie->secure, $updated_cookie->secure);
        self::assertSame($cookie->http_only, $updated_cookie->http_only);
        self::assertSame($cookie->same_site, $updated_cookie->same_site);
    }

    #[Test]
    public function valueConvertsStringableToString(): void
    {
        $cookie = new Cookie('test', new class implements \Stringable {
            public function __toString(): string
            {
                return 'stringable_value';
            }
        });

        self::assertSame('stringable_value', $cookie->value());
    }

    #[Test]
    public function setAddsCookieToResponse(): void
    {
        $response = new Response();
        $cookie = new Cookie('test', 'value');

        $updated_response = $cookie->set($response);

        self::assertNotSame($response, $updated_response);
        self::assertTrue($updated_response->hasHeader(HttpHeader::SET_COOKIE));

        $cookie_strings = $updated_response->getHeader(HttpHeader::SET_COOKIE);
        self::assertCount(1, $cookie_strings);
        self::assertStringContainsString('test=value', $cookie_strings[0]);
    }

    #[Test]
    #[DataProvider('cookieStringProvider')]
    public function toStringFormatsCookieCorrectly(
        Cookie $cookie,
        string $expected_string,
    ): void {
        $actual_string = $cookie->toString();
        self::assertStringContainsString($expected_string, $actual_string);
    }

    /**
     * @return \Iterator<string, array{Cookie, string}>
     */
    public static function cookieStringProvider(): \Iterator
    {
        yield 'basic cookie' => [
            new Cookie('test', 'value'),
            'test=value; Path=/; Secure; HttpOnly; SameSite=Lax',
        ];

        yield 'with domain' => [
            new Cookie('test', 'value', null, '/', 'example.com'),
            'test=value; Path=/; Domain=example.com; Secure; HttpOnly; SameSite=Lax',
        ];

        yield 'with TimeInterval' => [
            new Cookie('test', 'value', new TimeInterval(seconds: 3600)),
            'test=value; Max-Age=3600; Path=/; Secure; HttpOnly; SameSite=Lax',
        ];

        yield 'with SameSite strict' => [
            new Cookie('test', 'value', null, '/', '', true, true, SameSite::Strict),
            'test=value; Path=/; Secure; HttpOnly; SameSite=Strict',
        ];

        yield 'with SameSite none' => [
            new Cookie('test', 'value', null, '/', '', true, true, SameSite::None),
            'test=value; Path=/; Secure; HttpOnly; SameSite=None',
        ];

        yield 'not secure and not http only' => [
            new Cookie('test', 'value', null, '/', '', false, false, SameSite::Lax, false),
            'test=value; Path=/; SameSite=Lax',
        ];

        yield 'with partitioned' => [
            new Cookie('test', 'value', null, '/', '', true, true, SameSite::Lax, true),
            'test=value; Path=/; Secure; HttpOnly; SameSite=Lax; Partitioned',
        ];

        yield 'with value needing encoding' => [
            new Cookie('test', 'value with spaces & special chars'),
            'test=value%20with%20spaces%20%26%20special%20chars; Path=/; Secure; HttpOnly; SameSite=Lax',
        ];

        yield 'with raw value (no encoding)' => [
            new Cookie('test', 'value with spaces', null, '/', '', true, true, SameSite::Lax, false, true),
            'test=value with spaces; Path=/; Secure; HttpOnly; SameSite=Lax',
        ];

        yield 'empty value (deletion)' => [
            new Cookie('test', ''),
            'test=deleted; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=/; Secure; HttpOnly; SameSite=Lax',
        ];
    }
}
