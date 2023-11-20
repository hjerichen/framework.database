<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithIdsCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\User1;
use HJerichen\FrameworkDatabase\Test\Helpers\UserType;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTypeCollection;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;

class QueryWithIdsCommandTest extends DatabaseTestCase
{
    private QueryWithIdsCommand $queryCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryCommand = new QueryWithIdsCommand($this->connection);
    }

    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new MyTablesProvider();
    }

    protected function getDatasetForSetup(): Dataset
    {
        return new DatasetArray([
            'user' => [
                ['id' => 1, 'name' => 'jon', 'email' => 'test1@test.de', 'categories' => '["cat1","cat2"]', 'types' => '["type1"]'],
                ['id' => 2, 'name' => 'doe', 'email' => 'test2@test.de'],
                ['id' => 3, 'name' => 'doe', 'email' => 'test3@test.de'],
            ]
        ]);
    }

    /* TESTS */

    public function testSimpleQuery(): void
    {
        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test1@test.de';

        $user2 = new User();
        $user2->id = 3;
        $user2->name = 'doe';
        $user2->email = 'test3@test.de';

        $expected = [$user1, $user2];
        $actual = $this->queryCommand->execute(User::class, [1, 3]);
        self::assertEquals($expected, $actual);
    }

    public function testSimpleQueryForOne(): void
    {
        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test1@test.de';

        $expected = [$user1];
        $actual = $this->queryCommand->execute(User::class, [1]);
        self::assertEquals($expected, $actual);
    }

    public function testSimpleQueryWithSpecialTypeValues(): void
    {
        $user1 = new User1();
        $user1->id = 1;
        $user1->date = null;
        $user1->name = 'jon';
        $user1->type = UserType::TYPE1();
        $user1->types = new UserTypeCollection([UserType::TYPE1()]);
        $user1->email = 'test1@test.de';
        $user1->categories = ['cat1', 'cat2'];
        $user1->dateImmutable = null;
        $user1->product = null;
        $user1->products = null;

        $expected = [$user1];
        $actual = $this->queryCommand->execute(User1::class, [1]);
        self::assertEquals($expected, $actual);
    }
}
