<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase;

use HJerichen\Framework\Configuration\Configuration as FrameworkConfiguration;

class Configuration
{
    public function __construct(
        private FrameworkConfiguration $configuration
    ) {
    }

    public function getDatabaseUrl(): ?string
    {
        return $this->configuration->getCustomValue('database-url');
    }

    public function getSchemaTablesProviderClass(): ?string
    {
        return $this->configuration->getCustomValue('database-schema-tables-provider');
    }
}
