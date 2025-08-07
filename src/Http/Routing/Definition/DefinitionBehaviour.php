<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing\Definition;

use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Routing\Route;
use Psr\Http\Server\RequestHandlerInterface;

use function PhoneBurner\Pinch\Enum\enum_values;
use function PhoneBurner\Pinch\Type\narrow_class_string;
use function PhoneBurner\Pinch\Type\narrow_string;

/**
 * @phpstan-require-implements Definition
 */
trait DefinitionBehaviour
{
    private const string REGEX = '#^(?:\\\[a-zA-Z]|[a-zA-Z])(?:[\-_.\\\]?[a-zA-Z0-9]+)*$#';

    /**
     * @var array<string, mixed>
     */
    private array $attributes;

    /**
     * @var list<string&value-of<HttpMethod>>
     */
    private array $methods;

    private string $path;

    /**
     * @param array<string,mixed> $attributes
     */
    private function setAttributes(array $attributes): void
    {
        $route = narrow_string($attributes[Route::class] ?? '');
        if ($route !== '' && ! \preg_match(self::REGEX, $route)) {
            throw new \InvalidArgumentException('invalid name: ' . $route);
        }

        $request_handler = narrow_string($attributes[RequestHandlerInterface::class] ?? '');
        if ($request_handler !== '' && ! narrow_class_string(RequestHandlerInterface::class, $request_handler)) {
            throw new \InvalidArgumentException('handler must be type of: ' . RequestHandlerInterface::class);
        }

        $this->attributes = $attributes;
    }

    /**
     * @param HttpMethod|(value-of<HttpMethod>&string) ...$methods
     */
    private function setMethods(HttpMethod|string ...$methods): void
    {
        /** @var array<HttpMethod> $methods */
        $methods = \array_map(HttpMethod::instance(...), $methods);

        /** @var array<string&value-of<HttpMethod>> $methods */
        $methods = \array_unique(enum_values(...$methods));

        $this->methods = \array_values($methods);
    }

    /**
     * @param callable(static):static ...$callbacks
     */
    public function with(callable ...$callbacks): static
    {
        return \array_reduce(
            $callbacks,
            static fn(self $definition, callable $fn): static => $fn($definition),
            $this,
        );
    }
}
