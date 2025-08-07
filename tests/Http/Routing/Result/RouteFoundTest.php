<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\Result;

use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\Pinch\Component\Http\Routing\Result\RouteFound;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteFoundTest extends TestCase
{
    protected const array PATH_PARAMS = [
        'test' => 'data',
    ];

    protected const array ROUTE_ATTRIBUTES = [
        'route' => 'data',
    ];

    protected const array DEFAULT_ROUTE_ATTRIBUTES = [
        RequestHandlerInterface::class => NotFoundRequestHandler::class,
        MiddlewareInterface::class => [],
    ];

    private RouteDefinition $definition;

    #[\Override]
    protected function setUp(): void
    {
        $this->definition = RouteDefinition::get('/path', self::ROUTE_ATTRIBUTES);
    }

    #[Test]
    public function makeReturnsFound(): void
    {
        $sut = RouteFound::make($this->definition, self::PATH_PARAMS);
        self::assertTrue($sut->isFound());
    }

    #[Test]
    public function makeReturnsRouteMatch(): void
    {
        $sut = RouteFound::make($this->definition, self::PATH_PARAMS);

        $match = $sut->getRouteMatch();

        self::assertSame([...self::DEFAULT_ROUTE_ATTRIBUTES, ...self::ROUTE_ATTRIBUTES], $match->getAttributes());

        self::assertSame(self::PATH_PARAMS, $match->getPathParameters());
    }
}
