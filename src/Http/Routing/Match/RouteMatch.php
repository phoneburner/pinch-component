<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Match;

use PhoneBurner\Http\Message\UriWrapper;
use PhoneBurner\Pinch\Component\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\Pinch\Component\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\Pinch\Component\Http\Routing\Route;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function PhoneBurner\Pinch\Array\array_wrap;
use function PhoneBurner\Pinch\Type\is_class_string_of;
use function PhoneBurner\Pinch\Type\narrow_class_string;

class RouteMatch implements Route
{
    use UriWrapper;

    /**
     * @param array<string, string> $path_vars
     */
    public static function make(RouteDefinition $definition, array $path_vars = []): self
    {
        // the route definition provides ways to set these, but they aren't required
        $attributes = [
            RequestHandlerInterface::class => NotFoundRequestHandler::class,
            MiddlewareInterface::class => [],
            ...$definition->getAttributes(),
        ];

        // if this was already set, ensure it's an array
        $attributes[MiddlewareInterface::class] = array_wrap($attributes[MiddlewareInterface::class]);

        // order here is important, path params are not preserved when evolving
        // the definition, only when calling `withPathParameter()` or `withPathParameters()`
        return new self(
            $definition->withAttributes($attributes)->withPathParameters($path_vars),
            $path_vars,
        );
    }

    /**
     * @param array<string, string> $path_vars
     */
    private function __construct(public readonly RouteDefinition $definition, public readonly array $path_vars)
    {
        $this->setWrapped($this->definition->getWrapped());
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->definition->getAttributes();
    }

    /**
     * @return array<string, string>
     */
    public function getPathParameters(): array
    {
        return $this->path_vars;
    }

    public function getPathParameter(string $name, mixed $default = null): mixed
    {
        return $this->path_vars[$name] ?? $default;
    }

    /**
     * @return class-string<RequestHandlerInterface>
     */
    public function getHandler(): string
    {
        $handler = $this->getAttributes()[RequestHandlerInterface::class];
        \assert(narrow_class_string(RequestHandlerInterface::class, $handler));
        return $handler;
    }

    /**
     * @return array<class-string<MiddlewareInterface>>
     */
    public function getMiddleware(): array
    {
        /** @var array<class-string<MiddlewareInterface>> $middleware */
        $middleware = $this->getAttributes()[MiddlewareInterface::class];
        \assert(\is_array($middleware) && \array_all($middleware, static function (mixed $value, string|int $key): bool {
            return is_class_string_of(MiddlewareInterface::class, $value);
        }));
        return $middleware;
    }

    #[\Override]
    public function withPathParameter(string $name, string $value): self
    {
        return new self($this->definition->withPathParameter($name, $value), $this->path_vars);
    }

    /**
     * @param array<string, string> $params
     */
    #[\Override]
    public function withPathParameters(array $params): self
    {
        return new self($this->definition->withPathParameters($params), $this->path_vars);
    }

    #[\Override]
    protected function wrap(UriInterface $uri): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withScheme(string $scheme): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withUserInfo(string $user, string|null $password = null): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withHost(string $host): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withPort(int|null $port): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withPath(string $path): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withQuery(string $query): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }

    #[\Override]
    public function withFragment(string $fragment): never
    {
        throw new \LogicException(self::class . ' does not support URI with methods directly, use `getWrapped()` to get the underlying URI');
    }
}
