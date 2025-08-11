<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\Definition;

use PhoneBurner\Pinch\Component\Http\Routing\Definition\InMemoryDefinitionList;
use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteGroupDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function PhoneBurner\Pinch\Type\narrow_nonempty_string;

final class InMemoryDefinitionListTest extends TestCase
{
    /**
     * @var RouteDefinition[]
     */
    private array $expected_routes;

    private InMemoryDefinitionList $sut;

    #[\Override]
    protected function setUp(): void
    {
        $routes = [
            1 => RouteDefinition::get('/route1')->withName('route1'),
            2 => RouteDefinition::get('/route2')->withName('route2'),
            3 => RouteDefinition::get('/route3')->withName('route3'),
            4 => RouteDefinition::get('/route4')->withName('route4'),
            5 => RouteDefinition::get('/route5')->withName('route5'),
            6 => RouteDefinition::get('/route6')->withName('route6'),
            7 => RouteDefinition::get('/route7')->withName('route7'),
        ];

        $this->sut = InMemoryDefinitionList::make(
            $routes[1],
            $routes[2],
            RouteGroupDefinition::make('/group1')
            ->withName('group1')
            ->withRoutes(
                $routes[3],
                $routes[4],
            )->withGroups(RouteGroupDefinition::make('/group2')
            ->withName('group2')
            ->withRoutes(
                $routes[5],
                $routes[6],
            )),
            $routes[7],
        );

        $this->expected_routes = [
            1 => $routes[1],
            2 => $routes[2],
            3 => $routes[3]
                ->withRoutePath('/group1/route3')
                ->withName('group1.route3'),
            4 => $routes[4]
                ->withRoutePath('/group1/route4')
                ->withName('group1.route4'),
            5 => $routes[5]
                ->withRoutePath('/group1/group2/route5')
                ->withName('group1.group2.route5'),
            6 => $routes[6]
                ->withRoutePath('/group1/group2/route6')
                ->withName('group1.group2.route6'),
            7 => $routes[7],
        ];
    }

    #[Test]
    public function iteratorIsFlat(): void
    {
        self::assertEqualsCanonicalizing(
            \array_values($this->expected_routes),
            \array_values(\iterator_to_array($this->sut, false)),
        );
    }

    #[Test]
    public function getNamedRouteReturnsRouteDefinition(): void
    {
        foreach ($this->expected_routes as $route) {
            self::assertEquals($route, $this->sut->getNamedRoute(narrow_nonempty_string($route->getAttributes()[Route::class])));
        }
    }

    #[Test]
    public function hasNamedRouteReturnsTrueForExistingRoute(): void
    {
        foreach ($this->expected_routes as $route) {
            self::assertTrue($this->sut->hasNamedRoute(narrow_nonempty_string($route->getAttributes()[Route::class])));
        }

        self::assertFalse($this->sut->hasNamedRoute('not_a_route_that_exists'));
    }

    #[Test]
    public function serializationPreservesState(): void
    {
        $sut = \unserialize(\serialize($this->sut));
        self::assertInstanceOf(InMemoryDefinitionList::class, $sut);

        self::assertEquals(
            \iterator_to_array($this->sut, false),
            \iterator_to_array($sut, false),
        );
    }
}
