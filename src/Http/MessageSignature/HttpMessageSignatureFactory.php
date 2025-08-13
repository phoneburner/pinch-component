<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\MessageSignature;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use Psr\Http\Message\MessageInterface;

interface HttpMessageSignatureFactory
{
    /**
     * @template T of MessageInterface
     * @param T $message
     * @param array<string> $additional_headers
     * @return T
     */
    public function sign(
        MessageInterface $message,
        string $signature_input_name = 'sig1',
        array $additional_headers = [HttpHeader::CONTENT_TYPE, HttpHeader::IDEMPOTENCY_KEY],
    ): MessageInterface;

    public function verify(MessageInterface $message): bool;
}
