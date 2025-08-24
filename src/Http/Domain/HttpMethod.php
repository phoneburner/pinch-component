<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Domain;

use function PhoneBurner\Pinch\Enum\case_attr_fetch;

enum HttpMethod: string
{
    /**
     * Request a representation of the specified resource.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.1
     */
    #[HttpMethodMetadata(pure: true, idempotent: true, cacheable: true)]
    case Get = 'GET';

    /**
     * Ask for a response identical to that of the corresponding GET request,
     * but without the response body
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.2
     */
    #[HttpMethodMetadata(pure: true, idempotent: true, cacheable: true)]
    case Head = 'HEAD';

    /**
     * Used to submit an entity to the specified resource, often causing a
     * change in state or side effects on the server
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.3
     */
    #[HttpMethodMetadata(pure: false, idempotent: false, cacheable: true)]
    case Post = 'POST';

    /**
     * Replace the target resource with the request payload
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.4
     */
    #[HttpMethodMetadata(pure: false, idempotent: true, cacheable: false)]
    case Put = 'PUT';

    /**
     * Delete the specified resource
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.5
     */
    #[HttpMethodMetadata(pure: false, idempotent: true, cacheable: false)]
    case Delete = 'DELETE';

    /**
     * Establish a tunnel to the server identified by the target resource
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/CONNECT
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.6
     */
    #[HttpMethodMetadata(pure: false, idempotent: false, cacheable: false)]
    case Connect = 'CONNECT';

    /**
     * Describe the communication options for the target resource
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.7
     */
    #[HttpMethodMetadata(pure: true, idempotent: true, cacheable: false)]
    case Options = 'OPTIONS';

    /**
     * Perform a message loop-back test along the path to the target resource
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/TRACE
     * @link https://tools.ietf.org/html/rfc7231#section-4.3.8
     */
    #[HttpMethodMetadata(pure: true, idempotent: true, cacheable: false)]
    case Trace = 'TRACE';

    /**
     * Apply partial modifications to a resource
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PATCH
     * @link https://tools.ietf.org/html/rfc5789
     */
    #[HttpMethodMetadata(pure: false, idempotent: false, cacheable: false)]
    case Patch = 'PATCH';

    public static function instance(self|string $method): self
    {
        return $method instanceof self ? $method : self::from(\strtoupper($method));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return \array_column(self::cases(), 'value');
    }

    public function metadata(): HttpMethodMetadata
    {
        static $cache = [];
        return $cache[$this->value] ??= case_attr_fetch($this, HttpMethodMetadata::class);
    }
}
