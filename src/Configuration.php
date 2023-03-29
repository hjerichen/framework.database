<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase;

use HJerichen\Framework\Configuration\Configuration as FrameworkConfiguration;

class Configuration
{
    public function __construct(
        private readonly FrameworkConfiguration $configuration
    ) {
    }

    public function getDatabaseUrl(): string
    {
        return (string)$this->configuration->getCustomValue('database-url');
    }

    public function getSchemaTablesProviderClass(): ?string
    {
        $class = (string)$this->configuration->getCustomValue('database-schema-tables-provider');
        return $class ?: null;
    }
}
