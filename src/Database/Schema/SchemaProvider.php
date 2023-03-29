<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use HJerichen\FrameworkDatabase\ObjectFactory;
use HJerichen\ClassInstantiator\Attribute\Instantiator;

#[Instantiator(ObjectFactory::class)]
class SchemaProvider
{
    public function __construct(
        private readonly Connection $database,
        private readonly TablesProvider $tablesProvider,
    ) {
    }

    public function getWantedSchema(): Schema
    {
        $schema = new Schema();
        foreach ($this->tablesProvider->getSchemaTables() as $table) {
            $table->addToSchema($schema);
        }
        return $schema;
    }

    /** @throws Exception */
    public function getCurrentSchema(): Schema
    {
        return $this->database->createSchemaManager()->introspectSchema();
    }
}