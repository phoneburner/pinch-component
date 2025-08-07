<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Request;

use Psr\Http\Message\ServerRequestInterface;

interface RequestMutator
{
    public function mutate(ServerRequestInterface $request): ServerRequestInterface;
}
