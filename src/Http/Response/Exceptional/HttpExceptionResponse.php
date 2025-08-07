<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Response\Exceptional;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface HttpExceptionResponse extends Throwable, ResponseInterface
{
    public function getStatusCode(): int;

    public function getStatusTitle(): string;

    public function getStatusDetail(): string;

    /**
     * @return array<string, array<string>>
     */
    public function getHeaders(): array;

    /**
     * @return array<string, mixed>
     */
    public function getAdditional(): array;
}
