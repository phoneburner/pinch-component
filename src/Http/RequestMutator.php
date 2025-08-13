<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Psr\Http\Message\ServerRequestInterface;

interface RequestMutator
{
    public function mutate(ServerRequestInterface $request): ServerRequestInterface;
}
