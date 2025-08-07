<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\RequestHandler;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\FileNotFoundResponse;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\ServerErrorResponse;
use PhoneBurner\Pinch\Component\Http\Response\StreamResponse;
use PhoneBurner\Pinch\Component\Http\Routing\Domain\StaticFile;
use PhoneBurner\Pinch\Component\Http\Routing\Match\RouteMatch;
use PhoneBurner\Pinch\Component\Http\Routing\RequestHandler\StaticFileRequestHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

use const PhoneBurner\Pinch\UNIT_TEST_ROOT;

final class StaticFileRequestHandlerTest extends TestCase
{
    protected const string GOOD_FILE = UNIT_TEST_ROOT . '/Fixtures/2500x2500.png';

    private StaticFileRequestHandler $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new StaticFileRequestHandler();
    }

    #[Test]
    public function missingRouteMatchReturnsServerErrorResponse(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn(null);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(ServerErrorResponse::class, $response);
    }

    #[DataProvider('providesInvalidStaticFile')]
    #[Test]
    public function invalidStaticFileReturnsServerErrorResponse(array $attributes): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn($attributes);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(ServerErrorResponse::class, $response);
    }

    public static function providesInvalidStaticFile(): \Iterator
    {
        yield [[]];
        yield [[StaticFile::class => new \stdClass()]];
    }

    #[Test]
    public function badFileReturnsFileNotFoundResponse(): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn([
            StaticFile::class => new StaticFile('bad_file', ContentType::HTML),
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(FileNotFoundResponse::class, $response);
    }

    #[Test]
    public function validStaticFileReturnsInlineStreamResponse(): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn([
            StaticFile::class => new StaticFile(self::GOOD_FILE, ContentType::PNG),
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(StreamResponse::class, $response);
        self::assertSame(ContentType::PNG, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame((string)\filesize(self::GOOD_FILE), $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
        self::assertSame('inline', $response->getHeaderLine(HttpHeader::CONTENT_DISPOSITION));
        self::assertStringEqualsFile(self::GOOD_FILE, (string)$response->getBody());
    }

    #[Test]
    public function validStaticFileReturnsAttachmentStreamResponse(): void
    {
        $route_match = $this->createMock(RouteMatch::class);
        $route_match->method('getAttributes')->willReturn([
            StaticFile::class => new StaticFile(self::GOOD_FILE, ContentType::PNG),
            HttpHeader::CONTENT_DISPOSITION => 'attachment',
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteMatch::class)
            ->willReturn($route_match);

        $response = $this->sut->handle($request);

        self::assertInstanceOf(StreamResponse::class, $response);
        self::assertSame(ContentType::PNG, $response->getHeaderLine(HttpHeader::CONTENT_TYPE));
        self::assertSame((string)\filesize(self::GOOD_FILE), $response->getHeaderLine(HttpHeader::CONTENT_LENGTH));
        self::assertSame('attachment', $response->getHeaderLine(HttpHeader::CONTENT_DISPOSITION));
        self::assertStringEqualsFile(self::GOOD_FILE, (string)$response->getBody());
    }
}
