<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\ViewHelpers;

use Closure;
use Override;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
use Unleash\Client\Unleash;

final class GetVariantViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    #[Override]
    public function initializeArguments(): void
    {
        $this->registerArgument('feature', 'string', 'Feature name', true);
    }

    #[Override]
    public static function renderStatic(array $arguments, Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): ?string
    {
        $unleash = GeneralUtility::makeInstance(Unleash::class);
        assert($unleash instanceof Unleash);
        return $unleash->getVariant($arguments['feature'])->getPayload()?->getValue() ?: null;
    }
}
