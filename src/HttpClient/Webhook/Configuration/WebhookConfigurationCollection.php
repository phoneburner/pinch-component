<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\HttpClient\Webhook\Configuration;

/**
 * @template T of WebhookConfiguration
 * @extends \IteratorAggregate<T>
 */
interface WebhookConfigurationCollection extends \Countable, \IteratorAggregate
{
}
