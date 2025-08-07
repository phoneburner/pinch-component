<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Cookie;

use PhoneBurner\Pinch\Component\Http\Cookie\Cookie;
use PhoneBurner\Pinch\Component\Http\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CookieJarTest extends TestCase
{
    private CookieJar $jar;

    protected function setUp(): void
    {
        $this->jar = new CookieJar();
    }

    #[Test]
    public function itStartsEmpty(): void
    {
        self::assertCount(0, $this->jar);
        self::assertTrue($this->jar->isEmpty());
    }

    #[Test]
    public function itCanAddCookies(): void
    {
        $cookie1 = new Cookie('test1', 'value1');
        $cookie2 = new Cookie('test2', 'value2');

        $result = $this->jar->add($cookie1)->add($cookie2);

        // Fluent interface returns the jar itself
        self::assertSame($this->jar, $result);

        // Cookies are stored by name
        self::assertCount(2, $this->jar);
        self::assertSame($cookie1, $this->jar->get('test1'));
        self::assertSame($cookie2, $this->jar->get('test2'));
    }

    #[Test]
    public function addingCookieWithSameNameReplacesPrevious(): void
    {
        $cookie1 = new Cookie('test', 'value1');
        $cookie2 = new Cookie('test', 'value2');

        $this->jar->add($cookie1)->add($cookie2);

        self::assertCount(1, $this->jar);
        self::assertSame($cookie2, $this->jar->get('test'));
        self::assertSame('value2', $this->jar->get('test')->value());
    }

    #[Test]
    public function itCanRemoveCookies(): void
    {
        $cookie = new Cookie('test', 'value');
        $this->jar->add($cookie);

        $result = $this->jar->remove('test');

        // Fluent interface returns the jar itself
        self::assertSame($this->jar, $result);

        // Cookie is replaced with a removal cookie
        self::assertCount(1, $this->jar);
        $removal_cookie = $this->jar->get('test');
        self::assertInstanceOf(Cookie::class, $removal_cookie);
        self::assertSame('test', $removal_cookie->name);
        self::assertSame('', $removal_cookie->value());
    }

    #[Test]
    public function itCanBeIterated(): void
    {
        $cookie1 = new Cookie('test1', 'value1');
        $cookie2 = new Cookie('test2', 'value2');

        $this->jar->add($cookie1)->add($cookie2);

        $iterated = [];
        foreach ($this->jar as $key => $value) {
            $iterated[$key] = $value;
        }

        self::assertCount(2, $iterated);
        self::assertSame($cookie1, $iterated['test1']);
        self::assertSame($cookie2, $iterated['test2']);
    }
}
