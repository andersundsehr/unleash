<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\ViewHelpers;

use Override;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Unleash\Client\Unleash;

final class GetVariantViewHelper extends AbstractViewHelper
{
    public function __construct(private readonly Unleash $unleash)
    {
    }

    #[Override]
    public function initializeArguments(): void
    {
        $this->registerArgument('feature', 'string', 'Feature name', true);
    }

    public function render(): ?string
    {
        return $this->unleash->getVariant($this->arguments['feature'])->getPayload()?->getValue() ?: null;
    }
}
