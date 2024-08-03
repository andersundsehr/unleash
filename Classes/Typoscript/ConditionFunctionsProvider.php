<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Typoscript;

use Override;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Unleash\Client\Unleash;

#[Autoconfigure(public: true)]
final readonly class ConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function __construct(private Unleash $unleash)
    {
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            $this->getUnleashFunction(),
            $this->getUnleashVariantFunction(),
        ];
    }

    private function getUnleashFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'unleash',
            function (): void {
            },
            fn(array $existingVariables, $featureName, $default = false): bool => $this->unleash->isEnabled($featureName, default: (bool)$default)
        );
    }

    private function getUnleashVariantFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'unleashVariant',
            function (): void {
            },
            fn(array $existingVariables, $featureName): ?string => $this->unleash->getVariant($featureName)->getPayload()?->getValue() ?: null
        );
    }
}
