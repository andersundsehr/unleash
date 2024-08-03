<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Typoscript;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

final class TypoScriptConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [
            ConditionFunctionsProvider::class,
        ];
    }
}
