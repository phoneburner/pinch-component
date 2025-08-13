<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Message;

use Psr\Http\Message\MessageInterface;

/**
 * @template T of MessageInterface
 */
interface MessageArraySerializer
{
    /**
     * @param T $message
     */
    public function serialize(MessageInterface $message): array;

    /**
     * @return T
     */
    public function deserialize(array $message): MessageInterface;
}
