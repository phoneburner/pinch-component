<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface Router
{
    public function resolveForRequest(ServerRequestInterface $request): RouterResult;

    public function resolveByName(string $name): RouterResult;
}
