<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithFieldFilterCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;

class QueryWithFieldFilterCommandTest extends DatabaseTestCase
{
    private QueryWithFieldFilterCommand $queryCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryCommand = new QueryWithFieldFilterCommand($this->connection);
    }

    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new MyTablesProvider();
    }

    protected function getDatasetForSetup(): Dataset
    {
        return new DatasetArray([
            'user' => [
                ['id' => 1, 'name' => 'jon', 'email' => 'test1@test.com'],
                ['id' => 2, 'name' => 'doe', 'email' => 'test2@test.com'],
                ['id' => 3, 'name' => 'doe', 'email' => 'test3@test.com'],
            ]
        ]);
    }

    /* TESTS */

    public function testSimpleQuery(): void
    {
        $user1 = new User();
        $user1->id = 2;
        $user1->name = 'doe';
        $user1->email = 'test2@test.com';

        $user2 = new User();
        $user2->id = 3;
        $user2->name = 'doe';
        $user2->email = 'test3@test.com';

        $expected = [$user1, $user2];
        $actual = $this->queryCommand->execute(User::class, ['name' => 'doe']);
        self::assertEquals($expected, $actual);
    }
}
