<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Message;

use Laminas\Diactoros\Request\Serializer;
use PhoneBurner\Pinch\Component\Http\Stream\TemporaryStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

use function PhoneBurner\Pinch\Type\is_nonempty_string;

/**
 * @implements MessageSerializer<RequestInterface>
 */
class RequestSerializer implements MessageSerializer
{
    public function serialize(MessageInterface $message): string
    {
        if (! $message instanceof RequestInterface) {
            throw new \InvalidArgumentException('Message must be an instance of RequestInterface');
        }

        $serialized = Serializer::toString($message);
        \assert(is_nonempty_string($serialized));

        return $serialized;
    }

    public function deserialize(\Stringable|StreamInterface|string $message): RequestInterface
    {
        if (! $message instanceof StreamInterface) {
            $message = new TemporaryStream((string)$message);
        }

        return Serializer::fromStream($message);
    }
}
