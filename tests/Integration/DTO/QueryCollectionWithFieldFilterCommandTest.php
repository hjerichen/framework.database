<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryCollectionWithFieldFilterCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\UserDTOCollection;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;
use Override;

class QueryCollectionWithFieldFilterCommandTest extends DatabaseTestCase
{
    private QueryCollectionWithFieldFilterCommand $queryCommand;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->queryCommand = new QueryCollectionWithFieldFilterCommand($this->connection);
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
                ['id' => 3, 'name' => 'Grace', 'email' => 'grace.other@example.com'],
            ],
        ]);
    }

    public function testQueryReturnsDTOCollectionMatchingAllFields(): void
    {
        $actual = $this->queryCommand->execute(
            UserDTOCollection::class,
            ['name' => 'Grace', 'email' => 'grace@example.com'],
        );

        self::assertInstanceOf(UserDTOCollection::class, $actual);
        self::assertEquals([
            self::user(2, 'Grace', 'grace@example.com'),
        ], $actual->asArray());
    }

    private static function user(int $id, string $name, string $email): User
    {
        $user = new User();
        $user->id = $id;
        $user->name = $name;
        $user->email = $email;

        return $user;
    }
}
