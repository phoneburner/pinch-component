<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\Http\Event;

use Laminas\Diactoros\Response;
use PhoneBurner\Pinch\Component\Http\Event\EmittingHttpResponseCompleted;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmittingHttpResponseCompleteTest extends TestCase
{
    #[Test]
    public function constructorSetsPublicProperties(): void
    {
        $response = new Response();
        $event = new EmittingHttpResponseCompleted($response);

        self::assertSame($response, $event->response);
    }
}
