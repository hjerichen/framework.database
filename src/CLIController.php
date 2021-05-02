<?php declare(strict_types=1);
/**
 * @noinspection PhpUnused
 * @noinspection UnknownInspectionInspection
 */

namespace HJerichen\FrameworkDatabase;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\Framework\Response\Response;
use HJerichen\Framework\Response\TextResponse;
use Throwable;

class CLIController
{
    public function printWantedSchema(SchemaProvider $schemaProvider): Response
    {
        $schema = $schemaProvider->getWantedSchema();
        $queries = $schema->toSql(new MySQLPlatform);
        return $this->createResponseForQueries($queries);
    }

    /** @throws Exception */
    public function printCurrentSchema(SchemaProvider $schemaProvider): Response
    {
        $schema = $schemaProvider->getCurrentSchema();
        $queries = $schema->toSql(new MySQLPlatform);
        return $this->createResponseForQueries($queries);
    }

    /** @throws Exception */
    public function printUpdateSchema(SchemaSynchronizer $schemaSynchronizer): Response
    {
        $queries = $schemaSynchronizer->calculateQueries();
        return $this->createResponseForQueries($queries);
    }

    /** @throws Throwable */
    public function updateSchema(SchemaSynchronizer $schemaSynchronizer): Response
    {
        $queries = $schemaSynchronizer->execute();
        return $this->createResponseForQueries($queries);
    }

    private function createResponseForQueries(array $queries): Response
    {
        $sql = implode("\n", $queries);
        return new TextResponse("$sql\n");
    }
}