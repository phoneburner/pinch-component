<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Event;

use PhoneBurner\Pinch\Component\Http\RequestAware;
use PhoneBurner\Pinch\Component\Http\ResponseAware;
use PhoneBurner\Pinch\Component\HttpClient\Event\WebhookDeliveryCompleted;
use PhoneBurner\Pinch\Component\Logging\LogEntry;
use PhoneBurner\Pinch\Component\Logging\Loggable;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures\HttpFixtures;
use PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures\MockWebhookDeliveryMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WebhookDeliveryCompletedTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        self::assertSame($message, $event->message);
        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
        self::assertSame($elapsed_time, $event->elapsed_time);
    }

    #[Test]
    public function implementsLoggableInterface(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        self::assertInstanceOf(Loggable::class, $event);
    }

    #[Test]
    public function implementsRequestAwareInterface(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        self::assertInstanceOf(RequestAware::class, $event);
    }

    #[Test]
    public function implementsResponseAwareInterface(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        self::assertInstanceOf(ResponseAware::class, $event);
    }

    #[Test]
    public function getLogEntryReturnsValidLogEntry(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime(75000000); // 75ms

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);
        $log_entry = $event->getLogEntry();

        self::assertInstanceOf(LogEntry::class, $log_entry);
        self::assertSame(LogLevel::Info, $log_entry->level);
        self::assertSame('Webhook Request Delivery Completed', $log_entry->message);
    }

    #[Test]
    public function getLogEntryContainsCorrectContext(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime(100000000); // 100ms

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);
        $log_entry = $event->getLogEntry();

        self::assertArrayHasKey('webhook_id', $log_entry->context);
        self::assertArrayHasKey('webhook_url', $log_entry->context);
        self::assertArrayHasKey('attempt', $log_entry->context);
        self::assertArrayHasKey('elapsed_microtime', $log_entry->context);

        self::assertSame('10554035-5bcb-4c0a-8f74-fcd745268359', $log_entry->context['webhook_id']);
        self::assertSame('https://example.com/webhook', $log_entry->context['webhook_url']);
        self::assertSame(1, $log_entry->context['attempt']);
        self::assertSame(100000.0, $log_entry->context['elapsed_microtime']);
    }

    #[Test]
    #[DataProvider('provideVariousAttemptNumbers')]
    public function getLogEntryIncludesCorrectAttemptNumber(int $attempt): void
    {
        $message = MockWebhookDeliveryMessage::createWithAttempt($attempt);
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);
        $log_entry = $event->getLogEntry();

        self::assertSame($attempt, $log_entry->context['attempt']);
    }

    #[Test]
    #[DataProvider('provideVariousElapsedTimes')]
    public function getLogEntryIncludesCorrectElapsedTime(int $nanoseconds, float $expected_microseconds): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime($nanoseconds);

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);
        $log_entry = $event->getLogEntry();

        self::assertSame($expected_microseconds, $log_entry->context['elapsed_microtime']);
    }

    #[Test]
    public function readonlyPropertiesCannotBeModified(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        // Test that properties are readonly by trying to access them
        self::assertSame($message, $event->message);
        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
        self::assertSame($elapsed_time, $event->elapsed_time);
    }

    #[Test]
    public function eventIsImmutable(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        // Event should be immutable - properties should remain the same
        $original_message = $event->message;
        $original_request = $event->request;
        $original_response = $event->response;
        $original_elapsed_time = $event->elapsed_time;

        // Access properties multiple times to ensure they don't change
        self::assertSame($original_message, $event->message);
        self::assertSame($original_request, $event->request);
        self::assertSame($original_response, $event->response);
        self::assertSame($original_elapsed_time, $event->elapsed_time);
    }

    #[Test]
    #[DataProvider('provideVariousHttpResponses')]
    public function worksWithDifferentResponseTypes(int $status_code, string $reason_phrase): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse($status_code, $reason_phrase);
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        self::assertSame($response, $event->response);
        self::assertSame($status_code, $event->response->getStatusCode());
        self::assertSame($reason_phrase, $event->response->getReasonPhrase());
    }

    #[Test]
    #[DataProvider('provideVariousHttpRequests')]
    public function worksWithDifferentRequestTypes(string $method, string $uri): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest($method, $uri);
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();

        $event = new WebhookDeliveryCompleted($message, $request, $response, $elapsed_time);

        self::assertSame($request, $event->request);
        self::assertSame($method, $event->request->getMethod());
        self::assertSame($uri, (string)$event->request->getUri());
    }

    public static function provideVariousAttemptNumbers(): \Generator
    {
        yield 'first attempt' => [1];
        yield 'second attempt' => [2];
        yield 'third attempt' => [3];
        yield 'max retry attempt' => [5];
        yield 'high attempt number' => [10];
    }

    public static function provideVariousElapsedTimes(): \Generator
    {
        yield '1ms' => [1000000, 1000.0];
        yield '50ms' => [50000000, 50000.0];
        yield '100ms' => [100000000, 100000.0];
        yield '500ms' => [500000000, 500000.0];
        yield '1 second' => [1000000000, 1000000.0];
        yield '2.5 seconds' => [2500000000, 2500000.0];
    }

    public static function provideVariousHttpResponses(): \Generator
    {
        yield 'success 200' => [200, 'OK'];
        yield 'created 201' => [201, 'Created'];
        yield 'accepted 202' => [202, 'Accepted'];
        yield 'no content 204' => [204, 'No Content'];
        yield 'bad request 400' => [400, 'Bad Request'];
        yield 'unauthorized 401' => [401, 'Unauthorized'];
        yield 'forbidden 403' => [403, 'Forbidden'];
        yield 'not found 404' => [404, 'Not Found'];
        yield 'internal error 500' => [500, 'Internal Server Error'];
        yield 'bad gateway 502' => [502, 'Bad Gateway'];
        yield 'service unavailable 503' => [503, 'Service Unavailable'];
    }

    public static function provideVariousHttpRequests(): \Generator
    {
        yield 'POST to webhook' => ['POST', 'https://example.com/webhook'];
        yield 'PUT to api' => ['PUT', 'https://api.example.com/webhook'];
        yield 'PATCH to events' => ['PATCH', 'https://webhooks.example.com/events'];
        yield 'POST with path' => ['POST', 'https://example.com/api/v1/webhooks/receive'];
        yield 'different domain' => ['POST', 'https://hooks.example.org/incoming'];
    }
}
