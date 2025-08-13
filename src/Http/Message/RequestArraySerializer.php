<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Message;

use Laminas\Diactoros\Request\ArraySerializer;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @implements MessageArraySerializer<RequestInterface>
 */
class RequestArraySerializer implements MessageArraySerializer
{
    /**
     * @return array{
     *     method: string,
     *     request_target: string,
     *     uri: string,
     *     protocol_version: string,
     *     headers: array<array<string>>,
     *     body: string
     * }
     */
    public function serialize(MessageInterface $message): array
    {
        if (! $message instanceof RequestInterface) {
            throw new \InvalidArgumentException('Message must be an instance of ResponseInterface');
        }

        return ArraySerializer::toArray($message);
    }

    public function deserialize(array $message): RequestInterface
    {
        return ArraySerializer::fromArray($message);
    }
}
