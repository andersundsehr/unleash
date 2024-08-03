<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\ConfigurationModuleProvider;

use Andersundsehr\Unleash\Service\FeatureService;
use Andersundsehr\Unleash\Typo3UnleashContextProvider;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\AbstractProvider;

#[AutoconfigureTag(
    name: 'lowlevel.configuration.module.provider',
    attributes: [
        'identifier' => 'unleash',
        'label' => 'EXT:unleash - Features',
    ]
)]
final class UnleashConfigurationModuleProvider extends AbstractProvider
{
    public const ITERATIONS = 10;

    public function __construct(
        private readonly FeatureService $featureService,
        private readonly Typo3UnleashContextProvider $typo3UnleashContextProvider,
        private readonly ExtensionConfiguration $extensionConfiguration,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        $context = $this->typo3UnleashContextProvider->getContext()
            ->setIpAddress(null)
            ->setCurrentUserId(null);

        $analyticsForAll = $this->featureService->analyticsForAll(self::ITERATIONS, $context);
        $features = array_map(strip_tags(...), iterator_to_array($analyticsForAll));
        return [
            'title' => 'Features: ' . count($features),
            'description' => 'List of all features and their analytics (iterations: ' . self::ITERATIONS . ')',
            'important' => 'Context fields that are ignored for this overview: userId, clientIp',
            'features' => $features,
            'configuration' => $this->extensionConfiguration->get('unleash'),
            'context' => $context,
        ];
    }
}
