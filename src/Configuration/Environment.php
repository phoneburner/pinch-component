<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

use Psr\Container\ContainerInterface;

/**
 * Represents the environment and context in which the application is running.
 * This class is a container that that holds server and environment variables.
 * (The server is checked first before the environment.) It also defines some
 * methods for getting things like the root directory and hostname.
 */
interface Environment extends ContainerInterface
{
    // phpcs:ignore
    public BuildStage $stage { get; }

    // phpcs:ignore
    public Context $context { get; }

    // phpcs:ignore
    public string $root { get; }

    public function hostname(): string;

    public function server(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $integration = null,
    ): \UnitEnum|string|int|float|bool|null;

    public function env(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $integration = null,
    ): \UnitEnum|string|int|float|bool|null;

    public function match(mixed $production, mixed $development = null, mixed $integration = null): mixed;
}
