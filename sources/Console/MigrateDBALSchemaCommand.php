<?php

namespace Trompette\FeatureToggles\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trompette\FeatureToggles\DBAL\SchemaMigrator;

class MigrateDBALSchemaCommand extends Command
{
    /** @var SchemaMigrator[] */
    private $migrators;

    public function __construct(SchemaMigrator ...$migrators)
    {
        parent::__construct('feature-toggles:migrate-dbal-schema');

        $this->migrators = $migrators;
    }

    protected function configure()
    {
        $this->setDescription('Migrates DBAL schema');
        $this->setHelp('This command migrates a DBAL schema so that feature configurations can be persisted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->migrators as $migrator) {
            $migrator->migrateSchema();
            $output->writeln(sprintf("Schema migrated for <info>%s</info>", get_class($migrator)));
        }

        $output->writeln("All done!");

        return 0;
    }
}
