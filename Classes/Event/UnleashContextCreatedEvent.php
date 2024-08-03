<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Event;

use Unleash\Client\Configuration\Context;

/**
 * Event that is dispatched right after the UnleashContext is created with all the default values.
 * will be called multiple times, once per `->isEnabled` or `->getVariant` call.
 */
final class UnleashContextCreatedEvent
{
    public function __construct(public Context $context)
    {
    }
}
