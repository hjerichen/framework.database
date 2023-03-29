<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Comparator;
use HJerichen\FrameworkDatabase\ObjectFactory;
use HJerichen\ClassInstantiator\Attribute\Instantiator;
use Throwable;

#[Instantiator(ObjectFactory::class)]
class SchemaSynchronizer
{
    private AbstractPlatform $platform;

    public function __construct(
        private readonly Connection $database,
        private readonly SchemaProvider $schemaProvider,
    ) {
        $this->platform = new MySQLPlatform();
    }

    public function setPlatform(AbstractPlatform $platform): void
    {
        $this->platform = $platform;
    }

    /**
     * @return string[] The executed queries.
     * @throws Exception
     * @throws Throwable
     */
    public function execute(): array
    {
        $queries = $this->calculateQueries();
        $this->executeQueries($queries);

        return $queries;
    }

    /** @throws Exception */
    public function calculateQueries(): array
    {
        $currentSchema = $this->schemaProvider->getCurrentSchema();
        $wantedSchema =  $this->schemaProvider->getWantedSchema();
        $comparator = new Comparator();

        $diff = $comparator->compareSchemas($currentSchema, $wantedSchema);
        return $this->platform->getAlterSchemaSQL($diff);
    }

    /**
     * @param string[] $queries
     * @throws Throwable
     */
    private function executeQueries(array $queries): void
    {
        foreach ($queries as $query) {
            $this->database->executeQuery($query);
        }
    }
}