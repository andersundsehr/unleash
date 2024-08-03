<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Event;

use Unleash\Client\UnleashBuilder;

/**
 * Event that is dispatched right before the UnleashBuilder is built (`->build()`).
 */
final class UnleashBuilderBeforeBuildEvent
{
    public function __construct(public UnleashBuilder $builder)
    {
    }
}
