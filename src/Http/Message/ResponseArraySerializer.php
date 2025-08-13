<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Message;

use Laminas\Diactoros\Response\ArraySerializer;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @implements MessageArraySerializer<ResponseInterface>
 */
class ResponseArraySerializer implements MessageArraySerializer
{
    /**
     * @return array{
     *     status_code: int,
     *     reason_phrase: string,
     *     protocol_version: string,
     *     headers: array<array<string>>,
     *     body: string
     * }
     */
    public function serialize(MessageInterface $message): array
    {
        if (! $message instanceof ResponseInterface) {
            throw new \InvalidArgumentException('Message must be an instance of ResponseInterface');
        }

        return ArraySerializer::toArray($message);
    }

    public function deserialize(array $message): ResponseInterface
    {
        return ArraySerializer::fromArray($message);
    }
}
