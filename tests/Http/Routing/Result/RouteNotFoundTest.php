<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Routing\Result;

use LogicException;
use PhoneBurner\Pinch\Component\Http\Routing\Result\RouteNotFound;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouteNotFoundTest extends TestCase
{
    #[Test]
    public function makeReturnsFound(): void
    {
        $sut = RouteNotFound::make();
        self::assertFalse($sut->isFound());
    }

    #[Test]
    public function makeDoesNotReturnRouteMatch(): void
    {
        $sut = RouteNotFound::make();
        $this->expectException(LogicException::class);
        $sut->getRouteMatch();
    }
}
