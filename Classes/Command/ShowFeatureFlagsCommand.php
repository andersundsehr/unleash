<?php

declare(strict_types=1);

namespace Andersundsehr\Unleash\Command;

use Andersundsehr\Unleash\Service\FeatureService;
use Override;
use Andersundsehr\Unleash\Typo3UnleashContextProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Unleash\Client\Configuration\Context;

class ShowFeatureFlagsCommand extends Command
{
    public function __construct(
        private readonly Typo3UnleashContextProvider $typo3UnleashContextProvider,
        private readonly FeatureService $featureService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $features = $this->featureService->getAllFeatures();

        $this->setDescription('show current feature flags for this instance');
        $this->addArgument('features', InputArgument::IS_ARRAY, 'Feature flag to check eg. some-feature-flag', $features);

        $this->addOption('iterations', 'i', InputArgument::OPTIONAL, 'Number of iterations to check the feature flag', 20);

        $this->addOption('user', 'u', InputArgument::OPTIONAL, 'User ID to check the feature flag', '');
        $this->addOption('ip', '', InputArgument::OPTIONAL, 'IP address to check the feature flag', '');
        $this->addOption('host', '', InputArgument::OPTIONAL, 'Hostname to check the feature flag', '');
        $this->addOption('environment', 'e', InputArgument::OPTIONAL, 'Environment to check the feature flag (will not change the used environment in unleash, that is decided by the auth token)', '');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $features = $input->getArgument('features');
        $iterations = (int)$input->getOption('iterations');
        $context = $this->createContext($input);

        foreach ($this->featureService->analyticsForFeatures($features, $iterations, $context) as $feature => $message) {
            $output->writeln($feature . ': ' . $message);
        }

        return Command::SUCCESS;
    }

    private function createContext(InputInterface $input): Context
    {
        return $this->typo3UnleashContextProvider->getContext()
            ->setCurrentUserId($input->getOption('user') ?: null)
            ->setIpAddress($input->getOption('ip') ?: null)
            ->setHostname($input->getOption('host') ?: null)
            ->setEnvironment($input->getOption('environment') ?: null);
    }
}
