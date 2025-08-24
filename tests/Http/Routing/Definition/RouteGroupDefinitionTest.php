<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\Definition;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteGroupDefinition;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockRequestHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;

use function PhoneBurner\Pinch\Iterator\iter_generate;

final class RouteGroupDefinitionTest extends TestCase
{
    #[Test]
    public function makePrependsPathToAllRoutes(): void
    {
        $sut = RouteGroupDefinition::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/root/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/root/path2'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function makeAddsMethodsToAllRoutes(): void
    {
        $methods = [HttpMethod::Patch, HttpMethod::Trace];

        $sut = RouteGroupDefinition::make('', $methods);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post)
                ->withAddedMethod(...$methods),
            RouteDefinition::all('/path2'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function makeAddsAttributesToAllRoutes(): void
    {
        $attributes = [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ];

        $sut = RouteGroupDefinition::make('', [], $attributes);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value']),
            RouteDefinition::all('/path2', ['not_changed' => 'value']),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value'])->withAddedAttributes($attributes),
            RouteDefinition::all('/path2', ['not_changed' => 'value'])->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function serializationMaintainsState(): void
    {
        $route1 = RouteDefinition::get('/path1', ['replaced_attribute' => 'old_value']);
        $route2 = RouteDefinition::all('/path2', ['not_changed' => 'value']);

        $attributes = [
            'replaced_attribute' => 'new_value',
            'new_attribute' => 'value',
        ];

        $sut = RouteGroupDefinition::make('/root', [HttpMethod::Trace], $attributes)->withRoutes(
            $route1,
            $route2,
        );

        $sut = \unserialize(\serialize($sut));
        self::assertInstanceOf(RouteGroupDefinition::class, $sut);

        self::assertEqualsCanonicalizing([
            $route1->withRoutePath('/root/path1')
                ->withAddedMethod(HttpMethod::Trace)
                ->withAddedAttributes($attributes),
            $route2->withRoutePath('/root/path2')
                ->withAddedMethod(HttpMethod::Trace)
                ->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withPassesSelfToMethodsAndReturns(): void
    {
        $sut = RouteGroupDefinition::make('/test');

        $first = RouteGroupDefinition::make('/first');
        $second = RouteGroupDefinition::make('/second');
        $final = RouteGroupDefinition::make('/final');

        self::assertSame(
            $final,
            $sut->with(static function (RouteGroupDefinition $actual) use ($sut, $first): RouteGroupDefinition {
                self::assertSame($actual, $sut);
                return $first;
            }, static function (RouteGroupDefinition $actual) use ($first, $second): RouteGroupDefinition {
                self::assertSame($first, $actual);
                return $second;
            }, static function (RouteGroupDefinition $actual) use ($second, $final): RouteGroupDefinition {
                self::assertSame($second, $actual);
                return $final;
            }),
        );
    }

    #[Test]
    public function withRejectsAnyNoneSelfReturn(): void
    {
        $sut = RouteGroupDefinition::make('/test');

        $this->expectException(\TypeError::class);
        /** @phpstan-ignore argument.type (intentional defect for testing) */
        $sut->with(static fn(): RouteGroupDefinition => $sut, static fn(): \stdClass => new \stdClass());
    }

    #[Test]
    public function withRoutesReplacesRoutes(): void
    {
        $sut = RouteGroupDefinition::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        )->withRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedRoutesAddsRoute(): void
    {
        $sut = RouteGroupDefinition::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/root/path1')->withAddedMethod(HttpMethod::Post),
            RouteDefinition::all('/root/path2'),
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withGroupsReplacesGroups(): void
    {
        $sut = RouteGroupDefinition::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        /** @var MockObject&RouteGroupDefinition $group */
        $group = $this->createMock(RouteGroupDefinition::class);
        $group->method('getIterator')->willReturn(iter_generate([
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        ]));

        $with_groups = $sut->withGroups($group);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/root/path1'),
            RouteDefinition::all('/root/path2'),
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($with_groups));

        $without_groups = $with_groups->withGroups();

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
        ], \iterator_to_array($without_groups));
    }

    #[Test]
    public function withAddedGroupsAddsGroups(): void
    {
        $sut = RouteGroupDefinition::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        );

        /** @var MockObject&RouteGroupDefinition $group1 */
        $group1 = $this->createMock(RouteGroupDefinition::class);
        $group1->method('getIterator')->willReturn(new \ArrayIterator([
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        ]));

        $with_groups = $sut->withGroups($group1);

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
            RouteDefinition::get('/root/path1'),
            RouteDefinition::all('/root/path2'),
        ], \iterator_to_array($with_groups));

        /** @var MockObject&RouteGroupDefinition $group2 */
        $group2 = $this->createMock(RouteGroupDefinition::class);
        $group2->method('getIterator')->willReturn(new \ArrayIterator([
            RouteDefinition::get('/path5'),
            RouteDefinition::all('/path6'),
        ]));

        $with_more_groups = $with_groups->withAddedGroups($group2);

        self::assertEqualsCanonicalizing([
            RouteDefinition::head('/root/path3'),
            RouteDefinition::delete('/root/path4'),
            RouteDefinition::get('/root/path1'),
            RouteDefinition::all('/root/path2'),
            RouteDefinition::get('/root/path5'),
            RouteDefinition::all('/root/path6'),
        ], \iterator_to_array($with_more_groups));
    }

    #[Test]
    public function withRoutePathChangesPath(): void
    {
        $sut = RouteGroupDefinition::make('/root');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withRoutePath('/not_root');

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/not_root/path1'),
            RouteDefinition::all('/not_root/path2'),
            RouteDefinition::head('/not_root/path3'),
            RouteDefinition::delete('/not_root/path4'),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withMethodReplacesMethod(): void
    {
        $sut = RouteGroupDefinition::make('', [HttpMethod::Get]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withMethod(HttpMethod::Trace);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Trace),
            RouteDefinition::all('/path2')->withAddedMethod(HttpMethod::Trace),
            RouteDefinition::head('/path3')->withAddedMethod(HttpMethod::Trace),
            RouteDefinition::delete('/path4')->withAddedMethod(HttpMethod::Trace),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedMethodAddsMethod(): void
    {
        $sut = RouteGroupDefinition::make('', [HttpMethod::Get]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withAddedMethod(HttpMethod::Trace);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
            RouteDefinition::all('/path2')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
            RouteDefinition::head('/path3')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
            RouteDefinition::delete('/path4')->withAddedMethod(HttpMethod::Trace, HttpMethod::Get),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAttributesReplacesMergedArray(): void
    {
        $attributes = [
            'test' => 'new',
            'test2' => 'new',
        ];

        $sut = RouteGroupDefinition::make('', [], [
            'should' => 'not',
            'be' => 'used',
        ]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test' => 'old']),
            RouteDefinition::all('/path2'),
        )->withAttributes($attributes);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withAddedAttributes($attributes),
            RouteDefinition::all('/path2')->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedAttributesAddsMergedAttributes(): void
    {
        $attributes = [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
            'existing' => 'test_value',
        ];

        $sut = RouteGroupDefinition::make('', [], [
            'test_attribute2' => 'should_be_changed',
            'existing' => 'test_value',
        ]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value']),
            RouteDefinition::all('/path2', ['not_changed' => 'value']),
        )->withAddedAttributes([
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ]);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value'])->withAddedAttributes($attributes),
            RouteDefinition::all('/path2', ['not_changed' => 'value'])->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAttributeSetsAttribute(): void
    {
        $attributes = [
            'test_attribute' => 'new_value',
            'test_attribute2' => 'test_value2',
        ];

        $sut = RouteGroupDefinition::make('', [], [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ]);

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value']),
            RouteDefinition::all('/path2', ['not_changed' => 'value']),
        )->withAttribute('test_attribute', 'new_value');

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1', ['test_attribute' => 'old_value'])->withAddedAttributes($attributes),
            RouteDefinition::all('/path2', ['not_changed' => 'value'])->withAddedAttributes($attributes),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withHandlerSetsHandlerKey(): void
    {
        $sut = RouteGroupDefinition::make('');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1'),
            RouteDefinition::all('/path2'),
        )->withHandler(MockRequestHandler::class);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withHandler(MockRequestHandler::class),
            RouteDefinition::all('/path2')->withHandler(MockRequestHandler::class),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withMiddlewareAddsToMiddleware(): void
    {
        /**
         * @var class-string<MiddlewareInterface>&string $old_middleware_class
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $old_middleware_class = 'existing';

        /**
         * @var class-string<MiddlewareInterface> $new_middleware_class
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $new_middleware_class = 'new_middleware';

        $sut = RouteGroupDefinition::make('');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withMiddleware($old_middleware_class),
            RouteDefinition::all('/path2'),
        )->withMiddleware($new_middleware_class);

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withMiddleware($new_middleware_class, $old_middleware_class),
            RouteDefinition::all('/path2')->withMiddleware($new_middleware_class),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withAddedMiddlewareAppendsToMiddleware(): void
    {
        /**
         * @var class-string<MiddlewareInterface> $existing_middleware
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $existing_middleware = 'existing_middleware';

        /**
         * @var class-string<MiddlewareInterface> $new_middleware1
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $new_middleware1 = 'new_middleware1';

        /**
         * @var class-string<MiddlewareInterface> $new_middleware2
         * @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface
         */
        $new_middleware2 = 'new_middleware2';

        $sut = RouteGroupDefinition::make('', [], [
            MiddlewareInterface::class => [$existing_middleware],
        ]);

        $sut = $sut->withRoutes(
            /** @phpstan-ignore-next-line Intentional Defect - string is not a MiddlewareInterface */
            RouteDefinition::get('/path1')->withMiddleware('route_middleware'),
            RouteDefinition::all('/path2'),
        )->withAddedMiddleware($new_middleware1, $new_middleware2);

        self::assertEquals([
            RouteDefinition::get('/path1')->withAddedAttributes([
                MiddlewareInterface::class => [$existing_middleware, $new_middleware1, $new_middleware2, 'route_middleware'],
            ]),
            RouteDefinition::all('/path2')->withAddedAttributes([
                MiddlewareInterface::class => [$existing_middleware, $new_middleware1, $new_middleware2],
            ]),
        ], \iterator_to_array($sut));
    }

    #[Test]
    public function withNamePrependsName(): void
    {
        $sut = RouteGroupDefinition::make('');

        $sut = $sut->withRoutes(
            RouteDefinition::get('/path1')->withName('test'),
            RouteDefinition::all('/path2')->withName('test2'),
        )->withAddedRoutes(
            RouteDefinition::head('/path3'),
            RouteDefinition::delete('/path4'),
        )->withName('group');

        self::assertEqualsCanonicalizing([
            RouteDefinition::get('/path1')->withName('group.test'),
            RouteDefinition::all('/path2')->withName('group.test2'),
            RouteDefinition::head('/path3')->withName('group'),
            RouteDefinition::delete('/path4')->withName('group'),
        ], \iterator_to_array($sut));
    }
}
