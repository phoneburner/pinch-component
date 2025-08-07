<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\Definition;

use InvalidArgumentException;
use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\Domain\StaticFile;
use PhoneBurner\Pinch\Component\Http\Routing\RequestHandler\RedirectRequestHandler;
use PhoneBurner\Pinch\Component\Http\Routing\RequestHandler\StaticFileRequestHandler;
use PhoneBurner\Pinch\Component\Http\Routing\Route;
use PhoneBurner\Pinch\Component\Tests\Fixtures\MockRequestHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function PhoneBurner\Pinch\Enum\enum_values;

final class RouteDefinitionTest extends TestCase
{
    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function makeReturnsRouteDefinitionWithExpectedValues(array $test_case, array $methods): void
    {
        $sut = RouteDefinition::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());
    }

    #[Test]
    public function makeReturnsRouteDefinitionWithUniqueMethods(): void
    {
        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get, HttpMethod::Get, HttpMethod::Put, HttpMethod::Post, HttpMethod::Post],
        );

        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value, HttpMethod::Put->value, HttpMethod::Post->value], $sut->getMethods());
        self::assertSame([], $sut->getAttributes());
    }

    #[TestWith(['just a string'])]
    #[TestWith(['\stdClass'])]
    #[Test]
    public function makeRequiresHandlerToImplementInterfaceIfProvided(string $class): void
    {
        $this->expectException(InvalidArgumentException::class);

        RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            [
                RequestHandlerInterface::class => $class,
            ],
        );
    }

    #[DataProvider('provideRouteNames')]
    #[Test]
    public function makeRequiresValidNameIfProvided(string $name, bool $valid): void
    {
        if (! $valid) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            [
                Route::class => $name,
            ],
        );
    }

    #[DataProvider('provideTestDataWithNamedConstructors')]
    #[Test]
    public function namedConstructorsReturnRouteDefinitionWithExpectedValues(
        array $test_case,
        string $method,
        array $methods,
    ): void {
        $sut = RouteDefinition::$method(
            $test_case['path'],
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertSame($test_case['expected_attributes'], $sut->getAttributes());
    }

    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function serializationMaintainsState(array $test_case, array $methods): void
    {
        $sut = RouteDefinition::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());

        $sut = \unserialize(\serialize($sut));
        self::assertInstanceOf(RouteDefinition::class, $sut);

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertSame($test_case['expected_attributes'], $sut->getAttributes());
    }

    #[Test]
    public function serializationSetsWrappedUri(): void
    {
        $sut = RouteDefinition::get('/test');

        self::assertSame('/test', $sut->getPath());

        $sut = \unserialize(\serialize($sut));
        self::assertInstanceOf(RouteDefinition::class, $sut);

        self::assertSame('/test', $sut->getPath());
    }

    #[Test]
    public function getRoutePathReturnsPath(): void
    {
        $sut = RouteDefinition::make('/test_path', [HttpMethod::Get]);

        self::assertSame('/test_path', $sut->getRoutePath());
    }

    #[Test]
    public function getAttributesReturnsAttributes(): void
    {
        $attributes = [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ];

        $sut = RouteDefinition::make('/test_path', [HttpMethod::Get], $attributes);

        self::assertSame($attributes, $sut->getAttributes());
    }

    #[Test]
    public function getMethodsReturnsMethods(): void
    {
        $methods = [HttpMethod::Get, HttpMethod::Patch];
        $sut = RouteDefinition::make('/test_path', $methods);

        self::assertSame(
            \array_map(static fn(HttpMethod $method): string => $method->value, $methods),
            $sut->getMethods(),
        );
    }

    #[Test]
    public function withPassesSelfToMethodsAndReturns(): void
    {
        $sut = RouteDefinition::make('/test');

        $first = RouteDefinition::make('/first');
        $second = RouteDefinition::make('/second');
        $final = RouteDefinition::make('/final');

        self::assertSame(
            $final,
            $sut->with(static function (RouteDefinition $actual) use ($sut, $first): RouteDefinition {
                self::assertSame($actual, $sut);
                return $first;
            }, static function (RouteDefinition $actual) use ($first, $second): RouteDefinition {
                self::assertSame($first, $actual);
                return $second;
            }, static function (RouteDefinition $actual) use ($second, $final): RouteDefinition {
                self::assertSame($second, $actual);
                return $final;
            }),
        );
    }

    #[Test]
    public function withRejectsAnyNoneSelfReturn(): void
    {
        $sut = RouteDefinition::make('/test');

        $this->expectException(\TypeError::class);
        /** @phpstan-ignore argument.type (intentional defect for testing) */
        $sut->with(static fn(): RouteDefinition => $sut, static fn(): \stdClass => new \stdClass());
    }

    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function withPathParameterMaintainsState(array $test_case, array $methods): void
    {
        $sut = RouteDefinition::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());

        $new = $sut->withPathParameter('test', 'value');
        self::assertNotSame($sut, $new);

        self::assertSame($test_case['path'], $new->getRoutePath());
        self::assertEquals($methods, $new->getMethods());
        self::assertEquals($test_case['expected_attributes'], $new->getAttributes());
    }

    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function withPathParametersMaintainsState(array $test_case, array $methods): void
    {
        $sut = RouteDefinition::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());

        $new = $sut->withPathParameters([
            'test' => 'data',
        ]);
        self::assertNotSame($sut, $new);

        self::assertSame($test_case['path'], $new->getRoutePath());
        self::assertEquals($methods, $new->getMethods());
        self::assertEquals($test_case['expected_attributes'], $new->getAttributes());
    }

    #[Test]
    public function withAttributeAddsAttribute(): void
    {
        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            ['old' => 'data1'],
        );

        $new = $sut->withAttribute('new', 'data2');
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame(['old' => 'data1'], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertSame([
            'old' => 'data1',
            'new' => 'data2',
        ], $new->getAttributes());
    }

    #[Test]
    public function withAttributeReplacesAttribute(): void
    {
        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            [
                'old' => 'data1',
                'replace' => 'old data',
            ],
        );

        $new = $sut->withAttribute('replace', 'new data');
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame([
            'old' => 'data1',
            'replace' => 'old data',
        ], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertSame([
            'old' => 'data1',
            'replace' => 'new data',
        ], $new->getAttributes());
    }

    #[Test]
    public function withAttributesReplacesAttributeArray(): void
    {
        $old_attributes = [
            'old' => 'data1',
            'replace' => 'old data',
        ];

        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            $old_attributes,
        );

        $new_attributes = [
            'totally' => 'new',
            'set' => 'of data',
        ];

        $new = $sut->withAttributes($new_attributes);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame($old_attributes, $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertSame($new_attributes, $new->getAttributes());
    }

    #[Test]
    public function withAddedAttributesMergedAttributeArray(): void
    {
        $old_attributes = [
            'old' => 'data1',
            'replace' => 'old data',
        ];

        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            $old_attributes,
        );

        $new_attributes = [
            'totally' => 'new',
            'set' => 'of data',
            'replace' => 'new data',
        ];

        $new = $sut->withAddedAttributes($new_attributes);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame($old_attributes, $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertEqualsCanonicalizing([
            'old' => 'data1',
            'totally' => 'new',
            'set' => 'of data',
            'replace' => 'new data',
        ], $new->getAttributes());
    }

    #[DataProvider('provideChangedMethods')]
    #[Test]
    public function withMethodReplacesMethod(array $old_methods, array $new_methods, array $args): void
    {
        $sut = RouteDefinition::make(
            '/example',
            $old_methods,
            ['old' => 'data1'],
        );

        $new = $sut->withMethod(...$args);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertEquals(enum_values(...$old_methods), $sut->getMethods());
        self::assertSame(['old' => 'data1'], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertEqualsCanonicalizing(enum_values(...$new_methods), $new->getMethods());
        self::assertSame(['old' => 'data1'], $new->getAttributes());
    }

    #[DataProvider('provideAddedMethods')]
    #[Test]
    public function withAddedMethodAddsMethod(array $old_methods, array $new_methods, array $args): void
    {
        $sut = RouteDefinition::make(
            '/example',
            $old_methods,
            ['old' => 'data1'],
        );

        $new = $sut->withAddedMethod(...$args);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertEquals(\array_column($old_methods, 'value'), $sut->getMethods());
        self::assertSame(['old' => 'data1'], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertEqualsCanonicalizing(\array_column($new_methods, 'value'), $new->getMethods());
        self::assertSame(['old' => 'data1'], $new->getAttributes());
    }

    #[Test]
    public function withRoutePathReplacesPath(): void
    {
        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            ['old' => 'data'],
        );

        $new = $sut->withRoutePath('/new');
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame(['old' => 'data'], $sut->getAttributes());

        // changed
        self::assertSame('/new', $new->getRoutePath());
        self::assertEqualsCanonicalizing([HttpMethod::Get->value], $new->getMethods());
        self::assertSame(['old' => 'data'], $new->getAttributes());
    }

    #[DataProvider('provideNamedSettersAndValues')]
    #[Test]
    public function settersAddNamedAttribute(string $method, string $property, array $args, mixed $value): void
    {
        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            ['old' => 'data'],
        );

        $new = $sut->$method(...$args);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertEqualsCanonicalizing([
            'path' => '/example',
            'methods' => [HttpMethod::Get->value],
            'attributes' => ['old' => 'data'],
        ], $sut->__serialize());

        // changed
        self::assertEqualsCanonicalizing([
            'path' => '/example',
            'methods' => [HttpMethod::Get->value],
            'attributes' => [
                'old' => 'data',
                $property => $value,
            ],
        ], $new->__serialize());
    }

    #[DataProvider('provideRouteNames')]
    #[Test]
    public function withNameRequiresValidName(string $name, bool $valid): void
    {
        if (! $valid) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        RouteDefinition::make('/example', [HttpMethod::Get])->withName($name);
    }

    #[TestWith(['just a string'])]
    #[TestWith(['\stdClass'])]
    #[Test]
    public function withHandlerRequiresHandlerClass(string $class): void
    {
        $sut = RouteDefinition::make(
            '/example',
            [HttpMethod::Get],
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line Intentional Defect for Testing Sad Path */
        $sut->withHandler($class);
    }

    #[DataProvider('provideUriTestCase')]
    #[Test]
    public function wrappedUriHasExpectedPath(array $test_case): void
    {
        $sut = RouteDefinition::make(
            $test_case['path'],
            [HttpMethod::Get],
            [],
        );

        self::assertSame($test_case['uri_path'], (string)$sut);

        if ($test_case['templated_path']) {
            $uri = $sut;
            foreach ($test_case['template'] as [$method, $args]) {
                $uri = $uri->$method(...$args);
            }

            self::assertSame($test_case['templated_path'], (string)$uri);
        }
    }

    public static function provideRouteNames(): \Generator
    {
        $special = \str_split('\-_.');

        $words = ['test2', 'name1'];

        foreach ($special as $character) {
            $name = \implode($character, $words);
            yield $name => [$name, true];

            $name = \ucwords($name);
            yield $name => [$name, true];

            $name = \strtoupper($name);
            yield $name => [$name, true];

            $name = "\\" . $name;

            yield $name => [$name, true];
        }

        $bad_start = \str_split('1234567890-_.');

        foreach ($bad_start as $character) {
            $name = $character . 'test-name';
            yield $name => [$name, false];
        }

        $bad = ['💩', ...\str_split('!@#$%^&*()+[]{}:<>/|?')];

        foreach ($bad as $character) {
            $name = \implode($character, $words);
            yield $name => [$name, false];
        }
    }

    public static function provideUriTestCase(): \Generator
    {
        yield 'no vars' => [
            [
                'path' => '/test',
                'uri_path' => '/test',
                'templated_path' => '/test',
                'template' => [
                    ['withPathParameter', ['any', 'data']],
                ],
            ],
        ];

        $patterns = ['', ':\d+', ':(?:en|de)'];

        foreach ($patterns as $pattern) {
            $test_case = [
                'path' => '/test/{var' . $pattern . '}',
                'uri_path' => '/test/',
                'templated_path' => '/test/value',
            ];

            yield 'single var with param: var' . $pattern => [
                [...$test_case, 'template' => [
                    ['withPathParameter', ['var', 'value']],
                ]],
            ];

            yield 'single var with params: var' . $pattern => [
                [...$test_case, 'template' => [
                    ['withPathParameters', [['var' => 'value']]],
                ]],
            ];

            $test_case = [
                'path' => \sprintf('/test/{var1%s}/path/{var2%s}', $pattern, $pattern),
                'uri_path' => '/test//path/',
                'templated_path' => '/test/value',
            ];

            yield 'multiple var first with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value/path/',
                    'template' => [
                        ['withPathParameter', ['var1', 'value']],
                    ],
                ],
            ];

            yield 'multiple var second with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test//path/value',
                    'template' => [
                        ['withPathParameter', ['var2', 'value']],
                    ],
                ],
            ];

            yield 'multiple var both with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        ['withPathParameter', ['var2', 'value2']],
                        ['withPathParameter', ['var1', 'value1']],
                    ],
                ],
            ];

            yield 'multiple var both with params using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        [
                            'withPathParameters', [
                            [
                                'var1' => 'value1',
                                'var2' => 'value2',
                            ],
                            ],
                        ],
                    ],
                ],
            ];

            $test_case = [
                'path' => \sprintf('/test/[{var1%s}/]path/{var2%s}', $pattern, $pattern),
                'uri_path' => '/test/path/',
                'templated_path' => '/test/value',
                'evolve' => [
                    ['withHost', ['example.com']],
                    ['withScheme', ['https']],
                ],
            ];

            yield 'multiple optional var first with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value/path/',
                    'template' => [
                        ['withPathParameter', ['var1', 'value']],
                    ],
                ],
            ];

            yield 'multiple optional var second with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/path/value',
                    'template' => [
                        ['withPathParameter', ['var2', 'value']],
                    ],
                ],
            ];

            yield 'multiple optional var both with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        ['withPathParameter', ['var2', 'value2']],
                        ['withPathParameter', ['var1', 'value1']],
                    ],
                ],
            ];

            yield 'multiple optional var both with params using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        [
                            'withPathParameters', [
                            [
                                'var1' => 'value1',
                                'var2' => 'value2',
                            ],
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    public static function provideAddedMethods(): \Generator
    {
        yield 'adding post to get' => [
            [HttpMethod::Get],
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Post],
        ];

        yield 'adding delete to post and get' => [
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get, HttpMethod::Post, HttpMethod::Delete],
            [HttpMethod::Delete],
        ];

        yield 'adding delete and put to post and get' => [
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get, HttpMethod::Post, HttpMethod::Delete, HttpMethod::Put],
            [HttpMethod::Delete, HttpMethod::Put],
        ];

        yield 'adding get to post and get' => [
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get],
        ];
    }

    public static function provideChangedMethods(): \Generator
    {
        yield 'single to single' => [
            [HttpMethod::Get],
            [HttpMethod::Post],
            [HttpMethod::Post],
        ];

        yield 'single to multiple' => [
            [HttpMethod::Get],
            [HttpMethod::Post, HttpMethod::Get],
            [HttpMethod::Post, HttpMethod::Get],
        ];

        yield 'multiple to single' => [
            [HttpMethod::Post, HttpMethod::Get],
            [HttpMethod::Get],
            [HttpMethod::Get],
        ];

        yield 'multiple to multiple' => [
            [HttpMethod::Post , HttpMethod::Get ],
            [HttpMethod::Delete , HttpMethod::Patch ],
            [HttpMethod::Delete , HttpMethod::Patch ],
        ];

        yield 'duplicates' => [
            [HttpMethod::Post , HttpMethod::Get ],
            [HttpMethod::Delete , HttpMethod::Patch ],
            [HttpMethod::Delete , HttpMethod::Patch , HttpMethod::Delete , HttpMethod::Patch ],
        ];
    }

    public static function provideNamedSettersAndValues(): \Generator
    {
        yield 'withName' => [
            'withName',
            Route::class,
            ['named_route'],
            'named_route',
        ];

        yield 'withHandler' => [
            'withHandler',
            RequestHandlerInterface::class,
            [MockRequestHandler::class],
            MockRequestHandler::class,
        ];

        yield 'withMiddleware (string)' => [
            'withMiddleware',
            MiddlewareInterface::class,
            ['any string'],
            ['any string'],
        ];

        yield 'withMiddleware (array)' => [
            'withMiddleware',
            MiddlewareInterface::class,
            ['a', 'set', 'of', 'strings'],
            ['a', 'set', 'of', 'strings'],
        ];
    }

    public static function provideTestDataWithNamedConstructors(): \Generator
    {
        $named_constructors = ['get', 'head', 'post', 'put', 'patch', 'delete'];

        foreach (self::provideTestData() as $label => [$data]) {
            foreach ($named_constructors as $method) {
                yield $method . '() ' . $label => [
                    $data,
                    $method,
                    [HttpMethod::instance($method)->value],
                ];
            }

            yield 'all() ' . $label => [
                $data,
                'all',
                HttpMethod::values(),
            ];
        }
    }

    public static function provideTestDataWithMethod(): \Generator
    {
        foreach (self::provideTestData() as $label => [$data]) {
            foreach (HttpMethod::cases() as $method) {
                yield $method->value . ' to ' . $label => [
                    $data,
                    [$method->value],
                ];
            }

            yield 'all to ' . $label => [
                $data,
                HttpMethod::values(),
            ];
        }
    }

    /**
     * @return \Generator<string, array<array{path: string, attributes: mixed, expected_attributes: mixed}>>
     */
    public static function provideTestData(): \Generator
    {
        $paths = [
            'simple' => '/example',
            'variable' => '/example/{test}',
            'pattern variable' => '/user/{id:\d+}',
            'optional variable' => '/user/{id:\d+}[/{name}]',
            'optional variables' => '/user[/{id:\d+}[/{name}]]',
        ];

        $attribute_set = [
            'empty' => [],
            'simple' => ['test' => 'attribute'],
            'list' => ['test' => ['attribute', 'other']],
            'nexted' => ['test' => ['attribute' => 'other']],
            'mixed' => [
                'test' => ['attribute' => 'other'],
                'simple' => 'value',
                'int' => 1,
            ],
        ];

        foreach ($paths as $path_label => $path) {
            foreach ($attribute_set as $attribute_label => $attributes) {
                yield $path_label . ' path with ' . $attribute_label . ' attributes' => [
                    [
                        'path' => $path,
                        'attributes' => $attributes,
                        'expected_attributes' => $attributes,
                    ],
                ];

                yield $path_label . ' path with ' . $attribute_label . ' (iterable) attributes' => [
                    [
                        'path' => $path,
                        'attributes' => new \ArrayIterator($attributes),
                        'expected_attributes' => $attributes,
                    ],
                ];
            }
        }
    }

    #[TestWith([301])]
    #[TestWith([302])]
    #[TestWith([303])]
    #[TestWith([307])]
    #[TestWith([308])]
    #[Test]
    public function redirectCreatesRedirectRouteDefinition(int $status_code): void
    {
        $route_definition = RouteDefinition::redirect('/foo/bar[/index]', '/bar/foo', $status_code);

        self::assertSame(HttpMethod::values(), $route_definition->getMethods());
        self::assertSame('/foo/bar[/index]', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(RedirectRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertFalse($route_definition->hasAttribute(MiddlewareInterface::class));
        self::assertNull($route_definition->getAttribute(MiddlewareInterface::class));
        self::assertSame('/bar/foo', $route_definition->getAttribute(RedirectRequestHandler::URI));
        self::assertSame($status_code, $route_definition->getAttribute(RedirectRequestHandler::STATUS_CODE));
    }

    #[Test]
    public function redirectCreatesDefaultPermanentRedirectRouteDefinition(): void
    {
        $route_definition = RouteDefinition::redirect('/foo/bar[/index]', '/bar/foo');

        self::assertSame(HttpMethod::values(), $route_definition->getMethods());
        self::assertSame('/foo/bar[/index]', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(RedirectRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertFalse($route_definition->hasAttribute(MiddlewareInterface::class));
        self::assertNull($route_definition->getAttribute(MiddlewareInterface::class));
        self::assertSame('/bar/foo', $route_definition->getAttribute(RedirectRequestHandler::URI));
        self::assertSame(HttpStatus::PERMANENT_REDIRECT, $route_definition->getAttribute(RedirectRequestHandler::STATUS_CODE));
    }

    #[Test]
    public function fileCreatesRouteDefinitionForInlineStaticAsset(): void
    {
        $static_file = new StaticFile('foo/bar.html', ContentType::HTML);
        $route_definition = RouteDefinition::file('/foo/bar/baz', $static_file);

        self::assertSame([HttpMethod::Get->value], $route_definition->getMethods());
        self::assertSame('/foo/bar/baz', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(StaticFileRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertSame($static_file, $route_definition->getAttribute(StaticFile::class));
        self::assertNull($route_definition->getAttribute(HttpHeader::CONTENT_DISPOSITION));
    }

    #[Test]
    public function downloadCreatesRouteDefinitionForAttachmentStaticAsset(): void
    {
        $static_file = new StaticFile('foo/bar.html', ContentType::HTML);
        $route_definition = RouteDefinition::download('/foo/bar/baz', $static_file);

        self::assertSame([HttpMethod::Get->value], $route_definition->getMethods());
        self::assertSame('/foo/bar/baz', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(StaticFileRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertSame($static_file, $route_definition->getAttribute(StaticFile::class));
        self::assertSame('attachment', $route_definition->getAttribute(HttpHeader::CONTENT_DISPOSITION));
    }
}
