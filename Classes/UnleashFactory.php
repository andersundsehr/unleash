<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash;

use Andersundsehr\Unleash\Event\UnleashBuilderBeforeBuildEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use RuntimeException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Unleash\Client\Unleash;
use Unleash\Client\UnleashBuilder;

final readonly class UnleashFactory
{
    public function __construct(
        private ClientInterface $client,
        private Typo3UnleashContextProvider $typo3UnleashContextProvider,
        private EventDispatcherInterface $eventDispatcher,
        private ExtensionConfiguration $extensionConfiguration
    ) {
    }

    public function getConfig(string $key): string
    {
        return $this->extensionConfiguration->get('unleash', $key)
            ?: throw new RuntimeException('Missing ' . $key . ' in configuration');
    }

    public function __invoke(): Unleash
    {
        return $this->getUnleashBuilder()->build();
    }

    public function getUnleashBuilder(): UnleashBuilder
    {
        $builder = UnleashBuilder::create()
            ->withHttpClient($this->client)
            ->withAppUrl($this->getConfig('appUrl'))
            ->withAppName($this->getConfig('appName'))
            ->withInstanceId($this->getConfig('instanceId'))
            ->withContextProvider($this->typo3UnleashContextProvider)
            ->withHeader('Authorization', $this->getConfig('authorization'));

        /** @var UnleashBuilder $builder */
        $builder = $this->eventDispatcher->dispatch(new UnleashBuilderBeforeBuildEvent($builder))->builder;
        return $builder;
    }
}
