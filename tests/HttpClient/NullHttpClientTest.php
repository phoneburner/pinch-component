<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient;

use Laminas\Diactoros\Request;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\HttpClient\NullHttpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullHttpClientTest extends TestCase
{
    #[Test]
    public function sendRequestThrowsException(): void
    {
        $request = new Request('https://example.com', HttpMethod::Get->value);
        $client = new NullHttpClient();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('NullHttpClient does not support sending requests');
        $client->sendRequest($request);
    }
}
