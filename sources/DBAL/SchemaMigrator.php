<?php

namespace Trompette\FeatureToggles\DBAL;

interface SchemaMigrator
{
    public function migrateSchema(): void;
}
