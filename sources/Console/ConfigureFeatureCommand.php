<?php

namespace Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trompette\FeatureToggles\ToggleRouterInterface;

final class ConfigureFeatureCommand extends Command
{
    private ToggleRouterInterface $toggleRouter;

    public function __construct(ToggleRouterInterface $toggleRouter)
    {
        parent::__construct('feature-toggles:configure-feature');

        $this->toggleRouter = $toggleRouter;
    }

    protected function configure(): void
    {
        $this->setDescription('Configures a feature');
        $this->setHelp(
<<<HELP
The <info>%command.name%</info> command configures a feature for a strategy using the toggle router.

To enable or disable <comment>feature</comment> with <comment>onoff</comment> strategy:

  <info>%command.full_name% feature onoff on</info>
  <info>%command.full_name% feature onoff off</info>

To add or remove <comment>target</comment> from whitelist for <comment>feature</comment> with <comment>whitelist</comment> strategy:

  <info>%command.full_name% feature whitelist allow target</info>
  <info>%command.full_name% feature whitelist disallow target</info>

To configure percentage for <comment>feature</comment> with <comment>percentage</comment> strategy:

  <info>%command.full_name% feature percentage slide 50</info>
HELP
        );
        $this->addArgument('feature', InputArgument::REQUIRED, 'The feature name');
        $this->addArgument('strategy', InputArgument::REQUIRED, 'The strategy name');
        $this->addArgument('method', InputArgument::REQUIRED, 'The configuration method name');
        $this->addArgument('parameters', InputArgument::IS_ARRAY, 'Some extra parameters', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->toggleRouter->configureFeature(
            $feature = (string) filter_var($input->getArgument('feature'), FILTER_SANITIZE_STRING),
            (string) filter_var($input->getArgument('strategy'), FILTER_SANITIZE_STRING),
            (string) filter_var($input->getArgument('method'), FILTER_SANITIZE_STRING),
            $input->getArgument('parameters')
        );

        $output->writeln("Feature <info>$feature</info> configured!");

        return 0;
    }
}
