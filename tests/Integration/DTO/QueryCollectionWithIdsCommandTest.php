<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryCollectionWithIdsCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\UserDTOCollection;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;
use Override;

class QueryCollectionWithIdsCommandTest extends DatabaseTestCase
{
    private QueryCollectionWithIdsCommand $queryCommand;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->queryCommand = new QueryCollectionWithIdsCommand($this->connection);
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

    public function testQueryReturnsDTOCollectionForIds(): void
    {
        $actual = $this->queryCommand->execute(UserDTOCollection::class, [1, 3]);

        self::assertInstanceOf(UserDTOCollection::class, $actual);
        self::assertEquals([
            self::user(1, 'Ada', 'ada@example.com'),
            self::user(3, 'Katherine', 'katherine@example.com'),
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
