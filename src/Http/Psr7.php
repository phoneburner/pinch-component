<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use PhoneBurner\Pinch\Component\Http\Domain\ContentType;
use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\Stream\InMemoryStream;
use PhoneBurner\Pinch\Trait\HasNonInstantiableBehavior;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

use function PhoneBurner\Pinch\Math\is_between;

final readonly class Psr7
{
    use HasNonInstantiableBehavior;

    /**
     * @param ContentType::*&string $content_type
     */
    public static function expects(MessageInterface $message, string $content_type): bool
    {
        $headers = \array_filter(\array_map(
            static fn(string $header): string => \strtolower($message->getHeaderLine($header)),
            [HttpHeader::ACCEPT, HttpHeader::CONTENT_TYPE],
        ));

        if (\array_any($headers, static fn(string $header): bool => \str_contains($header, $content_type))) {
            return true;
        }

        // Handle content types defined with a structured syntax suffix, e.g. application/vnd.api+json
        $suffix = match ($content_type) {
            ContentType::JSON => '+json',
            ContentType::GZ => '+gzip',
            ContentType::ZIP => '+zip',
            ContentType::XML => '+xml',
            default => false,
        };

        return $suffix && \array_any($headers, static fn(string $header): bool => \str_contains($header, $suffix));
    }

    /**
     * If the request has an attribute with the name of the given class, and the
     * attribute's value is an instance of that class, return the attribute.
     * Otherwise, return null
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     */
    public static function attribute(string $class, ServerRequestInterface $request): object|null
    {
        $attribute = $request->getAttribute($class);
        return $attribute instanceof $class ? $attribute : null;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public static function jsonBodyToArray(MessageInterface|StreamInterface $message): array|null
    {
        try {
            $stream = $message instanceof MessageInterface ? $message->getBody() : $message;
            $decoded = \json_decode((string)$stream, true, 512, \JSON_THROW_ON_ERROR);
            return \is_array($decoded) ? $decoded : null;
        } catch (\JsonException) {
            return null;
        }
    }

    public static function isSuccessful(ResponseInterface $response): bool
    {
        return is_between($response->getStatusCode(), 200, 299);
    }

    /**
     * Convert a string or `Stringable` object to an instance of StreamInterface.
     * If the argument is already an instance of the PSR-7 StreamInterface, we return that same instance.
     * If you need a different instance, cast the $string argument to a string first.
     */
    public static function stream(\Stringable|string $string = ''): StreamInterface
    {
        return match (true) {
            \is_string($string) => InMemoryStream::make($string),
            $string instanceof StreamInterface => $string,
            default => InMemoryStream::make((string)$string),
        };
    }
}
