<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseMutator
{
    public function mutate(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface;
}
