<?php
/**
 * @noinspection UnknownInspectionInspection
 * @noinspection SqlResolve
 */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithSQLCommand;
use HJerichen\FrameworkDatabase\Test\DatabaseTestCase;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;

class QueryWithSQLCommandTest extends DatabaseTestCase
{
    private QueryWithSQLCommand $queryCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTable();

        $this->queryCommand = new QueryWithSQLCommand($this->connection);
    }

    protected function getDatasetForSetup(): Dataset
    {
        return new DatasetArray([
            'user' => [
                ['id' => 1, 'name' => 'jon', 'email' => 'test1@test.de'],
                ['id' => 2, 'name' => 'doe', 'email' => 'test2@test.de'],
                ['id' => 3, 'name' => 'doe', 'email' => 'test3@test.de'],
            ]
        ]);
    }

    /* TESTS */

    public function testSimpleQuery(): void
    {
        $user1 = new User();
        $user1->id = 2;
        $user1->name = 'doe';
        $user1->email = 'test2@test.de';

        $user2 = new User();
        $user2->id = 3;
        $user2->name = 'doe';
        $user2->email = 'test3@test.de';

        $sql = 'SELECT * FROM user WHERE id = :id OR email = :email';
        $parameters = ['id' => 3, 'email' => 'test2@test.de'];

        $expected = [$user1, $user2];
        $actual = $this->queryCommand->execute(User::class, $sql, $parameters);
        self::assertEquals($expected, $actual);
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
