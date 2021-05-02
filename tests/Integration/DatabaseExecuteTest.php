<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration;

use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\FrameworkDatabase\Test\DatabaseTestCase;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;

class DatabaseExecuteTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTable();
    }

    /* TESTS */

    public function testExecute(): void
    {
        $this->connection->insert('user', ['email' => 'test@test.de', 'name' => 'test']);

        $expected = new DatasetArray([
            'user' => [
                ['email' => 'test@test.de', 'name' => 'test']
            ]
        ]);
        $this->assertDatasetEqualsCurrent($expected);
    }

    /* HELPERS */

    private function setUpTable(): void
    {
        $tablesProvider = new MyTablesProvider();
        $schemaProvider = new SchemaProvider($this->connection, $tablesProvider);
        $schemaSynchronizer = new SchemaSynchronizer($this->connection, $schemaProvider);
        $schemaSynchronizer->execute();
    }
}