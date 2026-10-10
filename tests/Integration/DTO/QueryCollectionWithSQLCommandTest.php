<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryCollectionWithSQLCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\UserDTOCollection;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;
use Override;

class QueryCollectionWithSQLCommandTest extends DatabaseTestCase
{
    private QueryCollectionWithSQLCommand $queryCommand;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->queryCommand = new QueryCollectionWithSQLCommand($this->connection);
    }

    #[Override]
    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new MyTablesProvider();
    }

    #[Override]
    protected function getDatasetForSetup(): Dataset
    {
        return new DatasetArray([
            'user' => [
                ['id' => 1, 'name' => 'Ada', 'email' => 'ada@example.com'],
                ['id' => 2, 'name' => 'Grace', 'email' => 'grace@example.com'],
                ['id' => 3, 'name' => 'Katherine', 'email' => 'katherine@example.com'],
            ],
        ]);
    }

    public function testQueryReturnsHydratedDTOCollection(): void
    {
        $actual = $this->queryCommand->execute(
            UserDTOCollection::class,
            'SELECT * FROM user WHERE id = :id OR id = :otherId ORDER BY id',
            ['id' => 1, 'otherId' => 3],
        );

        self::assertInstanceOf(UserDTOCollection::class, $actual);
        self::assertCount(2, $actual);

        $expectedFirst = new User();
        $expectedFirst->id = 1;
        $expectedFirst->name = 'Ada';
        $expectedFirst->email = 'ada@example.com';

        $expectedSecond = new User();
        $expectedSecond->id = 3;
        $expectedSecond->name = 'Katherine';
        $expectedSecond->email = 'katherine@example.com';

        self::assertEquals([$expectedFirst, $expectedSecond], $actual->asArray());
    }
}
