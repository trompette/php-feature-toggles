<?php

namespace Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trompette\FeatureToggles\FeatureRegistry;
use Trompette\FeatureToggles\ToggleRouter;

class ShowFeatureConfigurationCommand extends Command
{
    private FeatureRegistry $featureRegistry;
    private ToggleRouter $toggleRouter;

    public function __construct(FeatureRegistry $featureRegistry, ToggleRouter $toggleRouter)
    {
        parent::__construct('feature-toggles:show-feature-configuration');

        $this->featureRegistry = $featureRegistry;
        $this->toggleRouter = $toggleRouter;
    }

    protected function configure(): void
    {
        $this->setDescription('Shows a feature configuration');
        $this->setHelp('This command shows a feature configuration and can answer if a target has a feature.');
        $this->addArgument('feature', InputArgument::REQUIRED, 'The feature name');
        $this->addArgument('target', InputArgument::OPTIONAL, 'An optional target');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $feature = (string) filter_var($input->getArgument('feature'), FILTER_SANITIZE_STRING);
        $target = (string) filter_var($input->getArgument('target'), FILTER_SANITIZE_STRING);

        $exists = $this->featureRegistry->exists($feature);
        $description = $exists ? $this->featureRegistry->getDefinition($feature)->getDescription() : '-';
        $strategy = $exists ? $this->featureRegistry->getDefinition($feature)->getStrategy() : '-';

        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');

        $output->writeln($formatter->formatBlock('Feature', 'comment', true));
        $output->writeln([
            "Name:        " . $this->formatValue($feature),
            "Registered:  " . $this->formatValue($exists),
            "Description: " . $this->formatValue($description),
            "Strategy:    " . $this->formatValue($strategy),
        ]);

        $output->writeln($formatter->formatBlock('Configuration', 'comment', true));
        $table = new Table($output);
        $table->setHeaders(['Strategy', 'Parameter', 'Value']);
        $table->setRows(iterator_to_array($this->generateRows($feature)));
        $table->render();

        if ('' !== $target) {
            $output->writeln($formatter->formatBlock('Target', 'comment', true));
            $output->writeln(sprintf(
                'Target %s %s feature %s.',
                $this->formatValue($target),
                $this->toggleRouter->hasFeature($target, $feature) ? 'has' : 'does not have',
                $this->formatValue($feature)
            ));
        }

        return 0;
    }

    /**
     * @return \Generator<int, array{string, string, string}>
     */
    private function generateRows(string $feature): \Generator
    {
        foreach ($this->toggleRouter->getFeatureConfiguration($feature) as $strategy => $configuration) {
            foreach ($configuration as $key => $value) {
                yield [$strategy, $key, $this->formatValue($value)];
            }
        }
    }

    /**
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        switch (true) {
            case is_bool($value):
                return $this->formatInfo($value ? 'yes' : 'no');

            case is_scalar($value):
                return $this->formatInfo((string) $value);

            case is_array($value) && count($value) <= 2:
                return $this->formatInfo(join(', ', $value));

            case is_array($value) && count($value) > 2:
                return $this->formatInfo(sprintf(
                    '%s, ... (%d more)',
                    join(', ', array_slice($value, 0, 2)),
                    count($value) - 2
                ));

            default:
                throw new \TypeError();
        }
    }

    private function formatInfo(string $value): string
    {
        return "<info>$value</info>";
    }
}
