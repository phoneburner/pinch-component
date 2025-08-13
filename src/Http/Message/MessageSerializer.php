<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @template T of MessageInterface
 */
interface MessageSerializer
{
    /**
     * @param T $message
     */
    public function serialize(MessageInterface $message): string;

    /**
     * @return T
     */
    public function deserialize(\Stringable|StreamInterface|string $message): MessageInterface;
}
