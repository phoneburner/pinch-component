<?php

declare(strict_types=1);

 namespace PhoneBurner\Pinch\Component\Http\Session;

use Psr\Http\Message\ServerRequestInterface;

interface SessionHandler extends \SessionHandlerInterface
{
/**
     * Called before reading from the session
     * The method signature is a bit unusual, and probably violates the open/closed
     * principle in order to maintain compatibility the PHP session handler interface.
     */
    public function open(
        string $path = '',
        string $name = SessionManager::SESSION_ID_COOKIE_NAME,
        SessionId|null $id = null,
        ServerRequestInterface|null $request = null,
    ): bool;

    /**
     * Called after writing to the session
     */
    public function close(): bool;

    public function read(SessionId|string $id): string;

    public function write(SessionId|string $id, string $data): bool;

    public function destroy(SessionId|string $id): bool;

/**
     * Default the cleanup behavior to the underlying service managing sessions
     */
    public function gc(int $max_lifetime): int;
}
