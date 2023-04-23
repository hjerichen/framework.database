<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase;

use HJerichen\Framework\Configuration\Configuration as FrameworkConfiguration;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use ReflectionClass;
use RuntimeException;

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

    /**
     * @return class-string<TablesProvider>|null
     * @psalm-suppress LessSpecificReturnStatement
     * @psalm-suppress MoreSpecificReturnType
     */
    public function getSchemaTablesProviderClass(): ?string
    {
        $class = $this->configuration->getCustomValue('database-schema-tables-provider');
        if (!$class || !is_string($class)) return null;

        if (!class_exists($class)) {
            throw new RuntimeException("Class $class does not exist.");
        }

        $reflection = new ReflectionClass($class);
        if (!$reflection->implementsInterface(TablesProvider::class)) {
            $interface = TablesProvider::class;
            throw new RuntimeException("Class $class does not implements $interface.");
        }

        return $class;
    }
}
