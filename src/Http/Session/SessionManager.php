<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Session;

use PhoneBurner\Pinch\Component\Http\Cookie\Cookie;
use PhoneBurner\Pinch\Component\Http\Exception\SessionAlreadyStarted;
use PhoneBurner\Pinch\Component\Http\Exception\SessionNotStarted;
use Psr\Http\Message\ServerRequestInterface;

interface SessionManager
{
    /**
     * @link https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html#session-id-name-fingerprinting
     * The session cookie name is intentionally kept generic to avoid fingerprinting.
     */
    public const string SESSION_ID_COOKIE_NAME = 'session_id';

    public const string XSRF_COOKIE_NAME = 'XSRF-TOKEN'; // Used for X-XSRF-TOKEN header

    /**
     * @throws SessionAlreadyStarted
     */
    public function start(ServerRequestInterface $request): SessionData;

    /**
     * @throws SessionNotStarted
     */
    public function save(): bool;

    /**
     * Clear the session data and regenerate the ID
     *
     * This should be called when "logging out" a user.
     *
     * We need to clear the existing session, as opposed to setting it to a new
     * instance because we expect consuming code to hold references to the specific
     * Session instance, e.g. as an attribute of the Request.
     *
     * Note: this method does require the session to be started, but we're relying
     * on that to be checked in the call to regenerate(). Consuming code should
     * always check started() before calling this method.
     *
     * @param bool $destroy_existing If true, the existing session record will be
     *  destroyed by the handler -- this does not affect the current session data
     * (which we are clearing anyway). This should probably always be true; however,
     * the option is exposed as that record around (temporarily) to prevent issues
     * with concurrent requests if the session is not locked or for debugging.
     * @throws SessionNotStarted
     */
    public function invalidate(bool $destroy_existing = true): SessionData;

    /**
     * Generate a new session ID for the existing session. This has the effect
     * of moving the data from one ID to another, if any data exists.
     *
     * This should be called whenever there is a change to the user's authentication
     * state, e.g. login, logout, or change of user permissions in order to prevent
     * session fixation attacks. This is called by the invalidate() method.
     *
     * @param bool $destroy_existing If true, the existing session record will be
     * destroyed by the handler -- this does not affect the current session data.
     * By default, we probably want to do this to prevent session fixation attacks,
     * but there could be cases where we want to keep that record around (temporarily)
     * to prevent issue with concurrent requests if the session is not locked or
     * for debugging.
     * @throws SessionNotStarted
     */
    public function regenerate(bool $destroy_existing = true): SessionData;

    /**
     * @throws SessionNotStarted
     */
    public function session(): SessionData;

    public function started(): bool;

    /**
     * @return array<Cookie>
     */
    public function cookies(): array;

    /**
     * This method is public, unlike the other similar methods, because we need to
     * decrypt the encrypted CSRF token when it is sent in the X-XSRF-TOKEN header
     * by something like Axios, as part of resolving and validating the CSRF token.
     */
    public function decryptXsrfToken(string|null $xsrf_token): CsrfToken|null;
}
