<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Middleware;

use PhoneBurner\Pinch\Component\Http\Exception\InvalidMiddlewareConfiguration;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function PhoneBurner\Pinch\Type\narrow;

/**
 * Given an array of MiddlewareInterface instances, or MiddlewareInterface class
 * name strings, this class will produce RequestHandler structures resolved from
 * the PSR-11 container. If the container happens to be a `\PhoneBurner\Pinch\Component\Container\LazyContainer`, then
 * the middleware will be resolved lazily.
 */
final readonly class LazyMiddlewareRequestHandlerFactory implements MiddlewareRequestHandlerFactory
{
    /**
     * @var \Closure(MiddlewareInterface): MiddlewareInterface
     */
    private \Closure $proxy_factory;

    public function __construct(
        private ContainerInterface $container,
        private EventDispatcherInterface $event_dispatcher,
    ) {
        // Note that since the Lazy Proxy factory cannot return a lazy object,
        // we need to call the `initializeLazyObject` method on the reflector of
        // the object to return the initialized object. If the object is not lazy,
        // this is a no-op.
        $this->proxy_factory = static fn(MiddlewareInterface $object): MiddlewareInterface => new \ReflectionClass($object)
            ->initializeLazyObject(narrow(MiddlewareInterface::class, $container->get($object::class)),);
    }

    /**
     * @param iterable<MiddlewareInterface|class-string<MiddlewareInterface>> $middleware_chain
     */
    #[\Override]
    public function queue(
        RequestHandlerInterface $fallback_handler,
        iterable $middleware_chain = [],
    ): MiddlewareQueue {
        $middleware_handler = MiddlewareQueue::make($fallback_handler, $this->event_dispatcher);
        foreach ($middleware_chain as $middleware) {
            $this->resolve($middleware_handler, $middleware);
        }

        return $middleware_handler;
    }

    /**
     * @param iterable<MiddlewareInterface|class-string<MiddlewareInterface>> $middleware_chain
     */
    #[\Override]
    public function stack(
        RequestHandlerInterface $fallback_handler,
        iterable $middleware_chain = [],
    ): MiddlewareStack {
        $middleware_handler = MiddlewareStack::make($fallback_handler, $this->event_dispatcher);
        foreach ($middleware_chain as $middleware) {
            $this->resolve($middleware_handler, $middleware);
        }

        return $middleware_handler;
    }

    private function resolve(
        MutableMiddlewareRequestHandler $handler,
        MiddlewareInterface|string $middleware,
    ): MutableMiddlewareRequestHandler {
        return match (true) {
            $middleware instanceof MiddlewareInterface => $handler->push($middleware),
            \is_a($middleware, MiddlewareInterface::class, true) => $this->pushMiddlewareClass($handler, $middleware),
            default => throw new InvalidMiddlewareConfiguration(ErrorMessage::RESOLUTION_ERROR),
        };
    }

    /**
     * @param class-string<MiddlewareInterface> $middleware_class
     */
    private function pushMiddlewareClass(
        MutableMiddlewareRequestHandler $handler,
        string $middleware_class,
    ): MutableMiddlewareRequestHandler {
        $reflection = new \ReflectionClass($middleware_class);
        return $handler->push(match ($reflection->isInstantiable()) {
            true => $reflection->newLazyProxy($this->proxy_factory),
            false => LazyMiddleware::make($this->container, $middleware_class),
        });
    }
}
