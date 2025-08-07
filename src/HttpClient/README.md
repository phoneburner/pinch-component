# HTTP Client Component

The HTTP Client component provides a PSR-18 compliant HTTP client implementation with event awareness and comprehensive testing support.

## Features

- **PSR-18 Compliant**: Fully compatible with PSR-18 HTTP Client interface
- **Event-Driven**: Emits events for request lifecycle (start, complete, failed)
- **Wrapper Pattern**: Wraps any PSR-18 client implementation
- **Null Implementation**: Includes `NullHttpClient` for testing purposes
- **Type-Safe**: Strict typing throughout with PHP 8.4 features
- **Comprehensive Logging**: Structured logging for all HTTP operations

## Components

### Interfaces

- `HttpClient`: Extends PSR-18 `ClientInterface` with event awareness documentation

### Implementations

- `EventAwareHttpClient`: Wraps any PSR-18 client and emits events
- `NullHttpClient`: Returns configurable empty responses (useful for testing)

### Events

- `HttpClientRequestStart`: Emitted before sending a request
- `HttpClientRequestComplete`: Emitted after receiving a response
- `HttpClientRequestFailed`: Emitted when a request fails

### Exceptions

- `HttpClientException`: Base exception for HTTP Client component

## Basic Usage

### Using EventAwareHttpClient

```php
use PhoneBurner\Pinch\Component\HttpClient\HttpClientWrapper;
use GuzzleHttp\Client as GuzzleClient;
use Psr\EventDispatcher\EventDispatcherInterface;

$httpClient = new HttpClientWrapper(
    new GuzzleClient(),
    $eventDispatcher
);

$request = new Request('https://api.example.com/users', 'GET');
$response = $httpClient->sendRequest($request);
```

### Using NullHttpClient for Testing

```php
use PhoneBurner\Pinch\Component\HttpClient\NullHttpClient;

// Returns 200 OK by default
$nullClient = new NullHttpClient();

// Returns custom status code and reason phrase
$notFoundClient = new NullHttpClient(404, 'Not Found');

$response = $nullClient->sendRequest($request);
echo $response->getStatusCode(); // 200 (or 404 with custom client)
```

## Event Handling

The HTTP Client emits three types of events during request processing:

### HttpClientRequestStart

```php
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestStart;

$eventDispatcher->addListener(HttpClientRequestStart::class, function (HttpClientRequestStart $event) {
    $request = $event->request;
    echo "Starting request to: " . $request->getUri();
});
```

### HttpClientRequestComplete

```php
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestComplete;

$eventDispatcher->addListener(HttpClientRequestComplete::class, function (HttpClientRequestComplete $event) {
    $request = $event->request;
    $response = $event->response;
    echo "Request completed with status: " . $response->getStatusCode();
});
```

### HttpClientRequestFailed

```php
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestFailed;

$eventDispatcher->addListener(HttpClientRequestFailed::class, function (HttpClientRequestFailed $event) {
    $request = $event->request;
    $exception = $event->exception;
    error_log("Request failed: " . $exception->getMessage());
});
```

## Framework Integration

When using the Pinch Framework, HTTP Client services are automatically configured:

```php
use PhoneBurner\Pinch\Component\HttpClient\HttpClient;
use Psr\Http\Client\ClientInterface;

// Both interfaces resolve to EventAwareHttpClient wrapping GuzzleHttp\Client
$httpClient = $app->get(HttpClient::class);
$psrClient = $app->get(ClientInterface::class);
```

### Configuration

Configure Guzzle options via application configuration:

```php
// config/http-client.php
return [
    'http_client' => [
        'guzzle' => [
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'headers' => [
                'User-Agent' => 'MyApp/1.0',
            ],
        ],
    ],
];
```

## Testing

### Unit Tests

The component includes comprehensive unit tests:

```bash
# Run HTTP Client tests
vendor/bin/phpunit packages/component/tests/HttpClient/
```

### Testing in Applications

Use `NullHttpClient` for testing:

```php
use PhoneBurner\Pinch\Component\HttpClient\NullHttpClient;

// In test setup
$mockClient = new NullHttpClient(200, 'OK');
$app->set(HttpClient::class, $mockClient);

// All HTTP requests will return 200 OK responses
```

## Advanced Usage

### Custom Event Listeners

Create event listeners for HTTP monitoring:

```php
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestStart;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestComplete;
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestFailed;

class HttpMetricsListener
{
    private array $metrics = [];

    public function onRequestStart(HttpClientRequestStart $event): void
    {
        $this->metrics['requests_started']++;
    }

    public function onRequestComplete(HttpClientRequestComplete $event): void
    {
        $this->metrics['requests_completed']++;
        $this->metrics['status_codes'][$event->response->getStatusCode()]++;
    }

    public function onRequestFailed(HttpClientRequestFailed $event): void
    {
        $this->metrics['requests_failed']++;
        $this->metrics['errors'][$event->exception::class]++;
    }
}
```

### Wrapper Client Pattern

Create custom HTTP client wrappers:

```php
use PhoneBurner\Pinch\Component\HttpClient\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RetryHttpClient implements HttpClient
{
    public function __construct(
        private HttpClient $client,
        private int $maxRetries = 3,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                return $this->client->sendRequest($request);
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }
                sleep(pow(2, $attempt)); // Exponential backoff
            }
        }
    }
}
```

## Error Handling

All HTTP Client implementations must throw PSR-18 compliant exceptions:

```php
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;

try {
    $response = $httpClient->sendRequest($request);
} catch (NetworkExceptionInterface $e) {
    // Network-related errors (DNS, connection timeout, etc.)
    error_log("Network error: " . $e->getMessage());
} catch (RequestExceptionInterface $e) {
    // Request-related errors (malformed request, etc.)
    error_log("Request error: " . $e->getMessage());
} catch (ClientExceptionInterface $e) {
    // All other HTTP client errors
    error_log("Client error: " . $e->getMessage());
}
```

## Logging

All events implement the `Loggable` interface and provide structured log entries:

```php
use PhoneBurner\Pinch\Component\HttpClient\Event\HttpClientRequestStart;

$event = new HttpClientRequestStart($request);
$logEntry = $event->getLogEntry();

// Log entry includes:
// - message: "HTTP Client Request Starting"
// - context: [method, uri, headers]
```

## Dependencies

- PSR-18 HTTP Client
- PSR-7 HTTP Messages
- PSR-14 Event Dispatcher
- PSR-3 Logger (for events)
- Laminas Diactoros (for PSR-7 implementations)

## Best Practices

1. **Always use event-aware clients** in production for monitoring and debugging
2. **Use NullHttpClient** for unit tests to avoid actual HTTP calls
3. **Implement proper error handling** for network and request exceptions
4. **Configure timeouts** appropriately for your use case
5. **Add custom event listeners** for metrics, logging, and monitoring
6. **Use wrapper pattern** to add functionality like retries, caching, etc.
