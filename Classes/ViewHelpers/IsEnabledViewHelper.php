<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\ViewHelpers;

use Override;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use Unleash\Client\Unleash;

final class IsEnabledViewHelper extends AbstractConditionViewHelper
{
    #[Override]
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('feature', 'string', 'Feature name', true);
        $this->registerArgument('default', 'bool', 'Default value if feature is not found', false, false);
    }

    /**
     * @param array{feature: string, default: bool} $arguments
     */
    #[Override]
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $unleash = GeneralUtility::makeInstance(Unleash::class);
        assert($unleash instanceof Unleash);
        return $unleash->isEnabled($arguments['feature'], default: (bool)$arguments['default']);
    }
}
