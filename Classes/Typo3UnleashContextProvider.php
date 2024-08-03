<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash;

use Override;
use Andersundsehr\Unleash\Event\UnleashCustomContextEvent;
use Andersundsehr\Unleash\Event\UnleashContextCreatedEvent;
use DateTimeInterface;
use JetBrains\PhpStorm\Pure;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Unleash\Client\Configuration\Context;
use Unleash\Client\Configuration\UnleashContext;
use Unleash\Client\ContextProvider\UnleashContextProvider;

final readonly class Typo3UnleashContextProvider implements UnleashContextProvider
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private \TYPO3\CMS\Core\Context\Context $typo3Context,
    ) {
    }

    #[Override]
    public function getContext(): Context
    {
        $context = new UnleashContext(
            $this->getUserId(),
            $this->getIpAddress(),
            $this->getSessionId(),
            $this->getCustomContext(),
            $this->getHostname(),
            $this->getEnvironment(),
            $this->getCurrentTime(),
        );

        return $this->eventDispatcher->dispatch(new UnleashContextCreatedEvent($context))->context;
    }

    /**
     * we decided to use the frontend user id as the user id for unleash
     * (backend user can be used as well, via `backendUser.id`)
     */
    private function getUserId(): ?string
    {
        return ((string)$this->typo3Context->getPropertyFromAspect('frontend.user', 'id') ?: null);
    }

    private function getIpAddress(): ?string
    {
        return GeneralUtility::getIndpEnv('REMOTE_ADDR') ?: null;
    }

    private function getSessionId(): ?string
    {
        // TODO implement session id
        return session_id() ?: null;
    }

    /**
     * @return array<string, string>
     */
    private function getCustomContext(): array
    {
        $customContext = [
            'backendUser.isLoggedIn' => ((string)$this->typo3Context->getPropertyFromAspect('backend.user', 'isLoggedIn') ?: null),
            'backendUser.id' => ((string)$this->typo3Context->getPropertyFromAspect('backend.user', 'id') ?: null),
            'backendUser.username' => ((string)$this->typo3Context->getPropertyFromAspect('backend.user', 'username') ?: null),
            'backendUser.isAdmin' => ((string)$this->typo3Context->getPropertyFromAspect('backend.user', 'isAdmin') ?: null),

            'frontendUser.isLoggedIn' => ((string)$this->typo3Context->getPropertyFromAspect('frontend.user', 'isLoggedIn') ?: null),
            'frontendUser.id' => ((string)$this->typo3Context->getPropertyFromAspect('frontend.user', 'id') ?: null),
            'frontendUser.username' => ((string)$this->typo3Context->getPropertyFromAspect('frontend.user', 'username') ?: null),
            'frontendUser.isAdmin' => ((string)$this->typo3Context->getPropertyFromAspect('frontend.user', 'isAdmin') ?: null),
        ];
        /** @var UnleashCustomContextEvent $event */
        $event = $this->eventDispatcher->dispatch(new UnleashCustomContextEvent($customContext));
        return array_filter($event->customContext);
    }

    private function getHostname(): ?string
    {
        return GeneralUtility::getIndpEnv('HTTP_HOST') ?: null;
    }

    private function getEnvironment(): ?string
    {
        // not that the environment used inside unleash is not used to determine the current environment
        // the used environment is selected by the authentication token instead
        $applicationContext = Environment::getContext();
        if ($applicationContext->isProduction()) {
            return 'production';
        }

        if ($applicationContext->isDevelopment()) {
            return 'development';
        }

        if ($applicationContext->isTesting()) {
            return 'testing';
        }

        return null;
    }

    private function getCurrentTime(): ?DateTimeInterface
    {
        return $this->typo3Context->getPropertyFromAspect('date', 'full');
    }
}
