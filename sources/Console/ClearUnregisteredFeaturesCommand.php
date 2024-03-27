<?php

namespace Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Trompette\FeatureToggles\ToggleRouterInterface;

final class ClearUnregisteredFeaturesCommand extends Command
{
    private ToggleRouterInterface $toggleRouter;

    public function __construct(ToggleRouterInterface $toggleRouter)
    {
        parent::__construct('feature-toggles:clear-unregistered-features');

        $this->toggleRouter = $toggleRouter;
    }

    protected function configure(): void
    {
        $this->setDescription('Clear configuration for unregistered features');
        $this->setHelp(
<<<HELP
The <info>%command.name%</info> command will clear the configuration for all
unregistered features.
HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $features = $this->toggleRouter->listUnregisteredFeatures();

        if (empty($features)) {
            $io->info('No unregistered feature found.');

            return 0;
        }

        $count = count($features);

        $io->note(sprintf(
            '%d unregistered feature(s) have been found: %s.',
            $count,
            implode(', ', $features),
        ));

        $question = sprintf(
            'WARNING! You are about to clear configuration for %d unregistered feature(s). Are you sure you wish to continue?',
            $count,
        );

        if (!$this->canExecute($question, $input, $io)) {
            $io->error('No feature configuration has been cleared.');

            return 1;
        }

        foreach ($features as $feature) {
            $this->toggleRouter->clearFeatureConfiguration($feature);
        }

        $io->success(sprintf(
            'Successfully cleared configuration for %d feature(s): %s.',
            $count,
            implode(', ', $features),
        ));

        return 0;
    }

    private function canExecute(string $question, InputInterface $input, SymfonyStyle $io): bool
    {
        return !$input->isInteractive() || $io->confirm($question);
    }
}
