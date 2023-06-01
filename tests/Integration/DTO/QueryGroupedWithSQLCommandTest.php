<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryGroupedWithSQLCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;

class QueryGroupedWithSQLCommandTest extends DatabaseTestCase
{
    private QueryGroupedWithSQLCommand $queryCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryCommand = new QueryGroupedWithSQLCommand($this->connection);
    }

    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new MyTablesProvider();
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
        $this->queryCommand->groupBy('name');

        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test1@test.de';

        $user2 = new User();
        $user2->id = 2;
        $user2->name = 'doe';
        $user2->email = 'test2@test.de';

        $user3 = new User();
        $user3->id = 3;
        $user3->name = 'doe';
        $user3->email = 'test3@test.de';

        $sql = 'SELECT * FROM user ORDER BY id';
        $parameters = ['id' => 3, 'email' => 'test2@test.de'];

        $expected = ['jon' => [$user1], 'doe' => [$user2, $user3]];
        $actual = $this->queryCommand->execute(User::class, $sql, $parameters);
        self::assertEquals($expected, $actual);
    }
}
