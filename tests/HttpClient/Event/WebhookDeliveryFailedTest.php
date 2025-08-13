<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Tests\HttpClient\Event;

use PhoneBurner\Pinch\Component\Http\Domain\HttpStatus;
use PhoneBurner\Pinch\Component\HttpClient\Event\WebhookDeliveryFailed;
use PhoneBurner\Pinch\Component\Logging\LogLevel;
use PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures\HttpFixtures;
use PhoneBurner\Pinch\Component\Tests\HttpClient\Fixtures\MockWebhookDeliveryMessage;
use PhoneBurner\Pinch\Time\Timer\ElapsedTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class WebhookDeliveryFailedTest extends TestCase
{
    #[Test]
    public function constructorSetsMessageProperty(): void
    {
        $message = new MockWebhookDeliveryMessage();

        $event = new WebhookDeliveryFailed($message);

        self::assertSame($message, $event->message);
    }

    #[Test]
    public function constructorSetsDefaultValues(): void
    {
        $message = new MockWebhookDeliveryMessage();

        $event = new WebhookDeliveryFailed($message);

        self::assertSame($message, $event->message);
        self::assertNull($event->request);
        self::assertNull($event->response);
        self::assertNull($event->elapsed_time);
        self::assertTrue($event->retryable);
        self::assertNull($event->exception);
    }

    #[Test]
    public function constructorSetsAllPropertiesWhenProvided(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse(500, 'Internal Server Error');
        $elapsed_time = HttpFixtures::createElapsedTime();
        $retryable = false;
        $exception = HttpFixtures::createException('Connection failed');

        $event = new WebhookDeliveryFailed(
            message: $message,
            request: $request,
            response: $response,
            elapsed_time: $elapsed_time,
            retryable: $retryable,
            exception: $exception,
        );

        self::assertSame($message, $event->message);
        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
        self::assertSame($elapsed_time, $event->elapsed_time);
        self::assertSame($retryable, $event->retryable);
        self::assertSame($exception, $event->exception);
    }

    #[Test]
    public function getLogEntryReturnsValidLogEntryForRetryableFailure(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, retryable: true);
        $log_entry = $event->getLogEntry();

        self::assertSame(LogLevel::Error, $log_entry->level);
        self::assertSame('Webhook Request Delivery Retryable Failure', $log_entry->message);
    }

    #[Test]
    public function getLogEntryReturnsValidLogEntryForPermanentFailure(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, retryable: false);
        $log_entry = $event->getLogEntry();

        self::assertSame(LogLevel::Error, $log_entry->level);
        self::assertSame('Webhook Request Delivery Permanent Failure', $log_entry->message);
    }

    #[Test]
    public function getLogEntryReturnsValidLogEntryForCriticalFailureWithException(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $exception = HttpFixtures::createException('Critical connection failure');
        $event = new WebhookDeliveryFailed($message, exception: $exception);
        $log_entry = $event->getLogEntry();

        self::assertSame(LogLevel::Critical, $log_entry->level);
        self::assertSame('Webhook Request Delivery Retryable Failure', $log_entry->message);
    }

    #[Test]
    public function getLogEntryContainsCorrectContextWithoutResponse(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message);
        $log_entry = $event->getLogEntry();

        self::assertArrayHasKey('webhook_id', $log_entry->context);
        self::assertArrayHasKey('webhook_url', $log_entry->context);
        self::assertArrayHasKey('attempt', $log_entry->context);
        self::assertArrayHasKey('status_code', $log_entry->context);

        self::assertSame('10554035-5bcb-4c0a-8f74-fcd745268359', $log_entry->context['webhook_id']);
        self::assertSame('https://example.com/webhook', $log_entry->context['webhook_url']);
        self::assertSame(1, $log_entry->context['attempt']);
        self::assertNull($log_entry->context['status_code']);
    }

    #[Test]
    public function getLogEntryContainsCorrectContextWithResponse(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $response = HttpFixtures::createMockResponse(500, 'Internal Server Error');
        $event = new WebhookDeliveryFailed($message, response: $response);
        $log_entry = $event->getLogEntry();

        self::assertArrayHasKey('webhook_id', $log_entry->context);
        self::assertArrayHasKey('webhook_url', $log_entry->context);
        self::assertArrayHasKey('attempt', $log_entry->context);
        self::assertArrayHasKey('status_code', $log_entry->context);

        self::assertSame('10554035-5bcb-4c0a-8f74-fcd745268359', $log_entry->context['webhook_id']);
        self::assertSame('https://example.com/webhook', $log_entry->context['webhook_url']);
        self::assertSame(1, $log_entry->context['attempt']);
        self::assertSame(500, $log_entry->context['status_code']);
    }

    #[Test]
    #[DataProvider('provideVariousAttemptNumbers')]
    public function getLogEntryIncludesCorrectAttemptNumber(int $attempt): void
    {
        $message = MockWebhookDeliveryMessage::createWithAttempt($attempt);
        $event = new WebhookDeliveryFailed($message);
        $log_entry = $event->getLogEntry();

        self::assertSame($attempt, $log_entry->context['attempt']);
    }

    #[Test]
    #[DataProvider('provideVariousResponseStatusCodes')]
    public function getLogEntryIncludesCorrectStatusCode(int $status_code): void
    {
        $message = new MockWebhookDeliveryMessage();
        $response = HttpFixtures::createMockResponse($status_code);
        $event = new WebhookDeliveryFailed($message, response: $response);
        $log_entry = $event->getLogEntry();

        self::assertSame($status_code, $log_entry->context['status_code']);
    }

    #[Test]
    #[DataProvider('provideRetryableScenarios')]
    public function getLogEntryMessageReflectsRetryableStatus(bool $retryable, string $expected_message): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, retryable: $retryable);
        $log_entry = $event->getLogEntry();

        self::assertSame($expected_message, $log_entry->message);
    }

    #[Test]
    #[DataProvider('provideLogLevelScenarios')]
    public function getLogEntryLevelDependsOnException(\Throwable|null $exception, LogLevel $expected_level): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, exception: $exception);
        $log_entry = $event->getLogEntry();

        self::assertSame($expected_level, $log_entry->level);
    }

    #[Test]
    public function readonlyPropertiesCannotBeModified(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();
        $retryable = false;
        $exception = HttpFixtures::createException();

        $event = new WebhookDeliveryFailed(
            message: $message,
            request: $request,
            response: $response,
            elapsed_time: $elapsed_time,
            retryable: $retryable,
            exception: $exception,
        );

        // Test that properties are readonly by trying to access them
        self::assertSame($message, $event->message);
        self::assertSame($request, $event->request);
        self::assertSame($response, $event->response);
        self::assertSame($elapsed_time, $event->elapsed_time);
        self::assertSame($retryable, $event->retryable);
        self::assertSame($exception, $event->exception);
    }

    #[Test]
    public function eventIsImmutable(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $request = HttpFixtures::createMockRequest();
        $response = HttpFixtures::createMockResponse();
        $elapsed_time = HttpFixtures::createElapsedTime();
        $retryable = true;
        $exception = HttpFixtures::createException();

        $event = new WebhookDeliveryFailed(
            message: $message,
            request: $request,
            response: $response,
            elapsed_time: $elapsed_time,
            retryable: $retryable,
            exception: $exception,
        );

        // Event should be immutable - properties should remain the same
        $original_message = $event->message;
        $original_request = $event->request;
        $original_response = $event->response;
        $original_elapsed_time = $event->elapsed_time;
        $original_retryable = $event->retryable;
        $original_exception = $event->exception;

        // Access properties multiple times to ensure they don't change
        self::assertSame($original_message, $event->message);
        self::assertSame($original_request, $event->request);
        self::assertSame($original_response, $event->response);
        self::assertSame($original_elapsed_time, $event->elapsed_time);
        self::assertSame($original_retryable, $event->retryable);
        self::assertSame($original_exception, $event->exception);
    }

    #[Test]
    public function handlesNullRequestGracefully(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, request: null);

        self::assertNull($event->request);
    }

    #[Test]
    public function handlesNullResponseGracefully(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, response: null);

        self::assertNull($event->response);
        $log_entry = $event->getLogEntry();
        self::assertNull($log_entry->context['status_code']);
    }

    #[Test]
    public function handlesNullElapsedTimeGracefully(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, elapsed_time: null);

        self::assertNull($event->elapsed_time);
    }

    #[Test]
    public function handlesNullExceptionGracefully(): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, exception: null);

        self::assertNull($event->exception);
        $log_entry = $event->getLogEntry();
        self::assertSame(LogLevel::Error, $log_entry->level);
    }

    #[Test]
    #[DataProvider('provideVariousExceptionTypes')]
    public function worksWithDifferentExceptionTypes(\Throwable $exception): void
    {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed($message, exception: $exception);

        self::assertSame($exception, $event->exception);
        $log_entry = $event->getLogEntry();
        self::assertSame(LogLevel::Critical, $log_entry->level);
    }

    #[Test]
    #[DataProvider('provideComplexFailureScenarios')]
    public function handlesComplexFailureScenarios(
        RequestInterface|null $request,
        ResponseInterface|null $response,
        ElapsedTime|null $elapsed_time,
        bool $retryable,
        \Throwable|null $exception,
        LogLevel $expected_level,
        string $expected_message_pattern,
    ): void {
        $message = new MockWebhookDeliveryMessage();
        $event = new WebhookDeliveryFailed(
            message: $message,
            request: $request,
            response: $response,
            elapsed_time: $elapsed_time,
            retryable: $retryable,
            exception: $exception,
        );

        $log_entry = $event->getLogEntry();
        self::assertSame($expected_level, $log_entry->level);
        self::assertMatchesRegularExpression($expected_message_pattern, (string)$log_entry->message);

        if ($response !== null) {
            self::assertSame($response->getStatusCode(), $log_entry->context['status_code']);
        } else {
            self::assertNull($log_entry->context['status_code']);
        }
    }

    public static function provideVariousAttemptNumbers(): \Generator
    {
        yield 'first attempt' => [1];
        yield 'second attempt' => [2];
        yield 'third attempt' => [3];
        yield 'max retry attempt' => [5];
        yield 'high attempt number' => [10];
    }

    public static function provideVariousResponseStatusCodes(): \Generator
    {
        yield 'bad request' => [HttpStatus::BAD_REQUEST];
        yield 'unauthorized' => [HttpStatus::UNAUTHORIZED];
        yield 'forbidden' => [HttpStatus::FORBIDDEN];
        yield 'not found' => [HttpStatus::NOT_FOUND];
        yield 'method not allowed' => [HttpStatus::METHOD_NOT_ALLOWED];
        yield 'timeout' => [HttpStatus::REQUEST_TIMEOUT];
        yield 'too many requests' => [HttpStatus::TOO_MANY_REQUESTS];
        yield 'internal server error' => [HttpStatus::INTERNAL_SERVER_ERROR];
        yield 'bad gateway' => [HttpStatus::BAD_GATEWAY];
        yield 'service unavailable' => [HttpStatus::SERVICE_UNAVAILABLE];
        yield 'gateway timeout' => [HttpStatus::GATEWAY_TIMEOUT];
    }

    public static function provideRetryableScenarios(): \Generator
    {
        yield 'retryable failure' => [true, 'Webhook Request Delivery Retryable Failure'];
        yield 'permanent failure' => [false, 'Webhook Request Delivery Permanent Failure'];
    }

    public static function provideLogLevelScenarios(): \Generator
    {
        yield 'no exception' => [null, LogLevel::Error];
        yield 'with exception' => [new \RuntimeException('Connection failed'), LogLevel::Critical];
        yield 'with logic exception' => [new \LogicException('Invalid configuration'), LogLevel::Critical];
        yield 'with invalid argument exception' => [new \InvalidArgumentException('Bad parameters'), LogLevel::Critical];
    }

    public static function provideVariousExceptionTypes(): \Generator
    {
        yield 'runtime exception' => [new \RuntimeException('Connection timeout')];
        yield 'logic exception' => [new \LogicException('Invalid webhook configuration')];
        yield 'invalid argument exception' => [new \InvalidArgumentException('Invalid parameters')];
        yield 'exception with previous' => [new \RuntimeException('Wrapper exception', 0, new \RuntimeException('Underlying cause'))];
    }

    public static function provideComplexFailureScenarios(): \Generator
    {
        yield 'connection failure - no response' => [
            HttpFixtures::createMockRequest(),
            null,
            null,
            true,
            new \RuntimeException('Connection failed'),
            LogLevel::Critical,
            '/Retryable Failure/',
        ];

        yield 'timeout failure - partial response' => [
            HttpFixtures::createMockRequest(),
            null,
            HttpFixtures::createElapsedTime(10000000000), // 10 seconds
            true,
            new \RuntimeException('Request timeout'),
            LogLevel::Critical,
            '/Retryable Failure/',
        ];

        yield 'server error - retryable' => [
            HttpFixtures::createMockRequest(),
            HttpFixtures::createMockResponse(500, 'Internal Server Error'),
            HttpFixtures::createElapsedTime(2000000000), // 2 seconds
            true,
            null,
            LogLevel::Error,
            '/Retryable Failure/',
        ];

        yield 'client error - permanent' => [
            HttpFixtures::createMockRequest(),
            HttpFixtures::createMockResponse(404, 'Not Found'),
            HttpFixtures::createElapsedTime(500000000), // 500ms
            false,
            null,
            LogLevel::Error,
            '/Permanent Failure/',
        ];

        yield 'configuration error - permanent with exception' => [
            null,
            null,
            null,
            false,
            new \InvalidArgumentException('Invalid webhook URL'),
            LogLevel::Critical,
            '/Permanent Failure/',
        ];
    }
}
