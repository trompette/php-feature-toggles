<?php

namespace Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trompette\FeatureToggles\ToggleRouter;

class ConfigureFeatureCommand extends Command
{
    /** @var ToggleRouter */
    private $toggleRouter;

    public function __construct(ToggleRouter $toggleRouter)
    {
        parent::__construct('feature-toggles:configure-feature');

        $this->toggleRouter = $toggleRouter;
    }

    protected function configure()
    {
        $this->setDescription('Configures a feature');
        $this->setHelp('This command configures a feature for a strategy using the toggle router.');
        $this->addArgument('feature', InputArgument::REQUIRED, 'The feature name');
        $this->addArgument('strategy', InputArgument::REQUIRED, 'The strategy name');
        $this->addArgument('method', InputArgument::REQUIRED, 'The configuration method name');
        $this->addArgument('parameters', InputArgument::IS_ARRAY, 'Some extra parameters', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->toggleRouter->configureFeature(
            $feature = $input->getArgument('feature'),
            $input->getArgument('strategy'),
            $input->getArgument('method'),
            $input->getArgument('parameters')
        );

        $output->writeln("Feature <info>$feature</info> configured!");

        return 0;
    }
}
