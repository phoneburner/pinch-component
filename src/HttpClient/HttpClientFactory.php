<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient;

interface HttpClientFactory
{
    /**
     * @param float $request_timeout_seconds // indefinite timeout by default
     * @param float $connect_timeout_seconds // indefinite timeout by default
     * @param bool $enable_ssl_verification // don't disable this unless you are a complete idiot.
     */
    public function createHttpClient(
        float $request_timeout_seconds = 0.0,
        float $connect_timeout_seconds = 0.0,
        bool $enable_ssl_verification = true,
    ): HttpClient;
}
