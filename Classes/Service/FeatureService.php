<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Service;

use Andersundsehr\Unleash\UnleashFactory;
use Generator;
use Unleash\Client\Configuration\Context;
use Unleash\Client\Unleash;

final readonly class FeatureService
{
    public function __construct(
        private UnleashFactory $unleashFactory,
        private Unleash $unleash,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getAllFeatures(): array
    {
        $features = [];
        foreach ($this->unleashFactory->getUnleashBuilder()->buildRepository()->getFeatures() as $feature) {
            $features[] = $feature->getName();
        }

        return $features;
    }

    /**
     * @return Generator<string, string>
     */
    public function analyticsForAll(int $iterations = 10, ?Context $context = null): Generator
    {
        return $this->analyticsForFeatures($this->getAllFeatures(), $iterations, $context);
    }

    /**
     * @param list<string> $features
     * @return Generator<string, string>
     */
    public function analyticsForFeatures(array $features, int $iterations = 10, ?Context $context = null): Generator
    {
        foreach ($features as $feature) {
            yield $feature => $this->analyticsForFeature($feature, $iterations, $context);
        }
    }

    private function analyticsForFeature(string $feature, int $iterations, ?Context $context = null): string
    {
        $count = 0;
        $variantValues = [];
        for ($i = 0; $i < $iterations; $i++) {
            $count += (int)$this->unleash->isEnabled($feature, $context);
            $variantValue = $this->unleash->getVariant($feature, $context)->getPayload()?->getValue();
            if ($variantValue !== null) {
                $variantValues[$variantValue] ??= 0;
                $variantValues[$variantValue]++;
            }
        }

        if ($variantValues) {
            return $this->combineVariantValues($variantValues, $iterations);
        }

        if ($count === $iterations) {
            return '<info>Enabled</info>';
        }

        if ($count === 0) {
            return '<error>Disabled</error>';
        }

        return '<comment>' . $this->percent($count, $iterations) . '%</comment> enabled';
    }


    /**
     * @param array<string, int> $variantValues
     */
    private function combineVariantValues(array $variantValues, int $maxTotal): string
    {
        $totalCount = 0;
        $messages = [];
        asort($variantValues, SORT_DESC);
        foreach ($variantValues as $variantValue => $count) {
            $messages[] = '<info>' . $variantValue . ': ' . $this->percent($count, $maxTotal) . '%</info>';
            $totalCount += $count;
        }

        if ($totalCount !== $maxTotal) {
            $messages[] = '<error>Off: ' . $this->percent($maxTotal - $totalCount, $maxTotal) . '%</error>';
        }

        return implode(' ', $messages);
    }


    private function percent(int $count, int $maxTotal): float
    {
        return round($count / $maxTotal * 100, 1);
    }
}
