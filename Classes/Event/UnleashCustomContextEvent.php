<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Event;

/**
 * This event is dispatched when the Unleash context is created.
 * use this if you only want to overwrite or add customContext data
 * if you want to change anything else, use the `UnleashContextCreatedEvent`
 */
final class UnleashCustomContextEvent
{
    public function __construct(
        /**
         * @var array<string, string|null>
         */
        public array $customContext
    ) {
    }
}
