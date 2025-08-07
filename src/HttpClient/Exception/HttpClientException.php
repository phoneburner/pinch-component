<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Exception;

use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;

/**
 * Base exception for HTTP Client component
 */
class HttpClientException extends RuntimeException implements ClientExceptionInterface
{
}
