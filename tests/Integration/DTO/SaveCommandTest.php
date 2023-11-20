<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use DateTime;
use DateTimeImmutable;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithIdsCommand;
use HJerichen\FrameworkDatabase\DTO\Save\SaveCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\Product;
use HJerichen\FrameworkDatabase\Test\Helpers\ProductCollection;
use HJerichen\FrameworkDatabase\Test\Helpers\User1;
use HJerichen\FrameworkDatabase\Test\Helpers\UserType;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTypeCollection;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;

class SaveCommandTest extends DatabaseTestCase
{
    private SaveCommand $saveCommand;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saveCommand = new SaveCommand($this->connection);
    }

    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new MyTablesProvider();
    }

    /* TESTS */

    public function testInsertUsers(): void
    {
        $product1 = new Product();
        $product1->id = 10;
        $product1->ean = 'test1';

        $product2 = new Product();
        $product2->id = 11;
        $product2->ean = 'test2';

        $user1 = new User1();
        $user1->name = 'jon';
        $user1->email = 'test1';
        $user1->types = new UserTypeCollection([UserType::TYPE1(), UserType::TYPE2()]);
        $user1->dateImmutable = null;
        $user1->categories = ['cat1', 'cat2'];
        $user1->product = $product1;
        $user1->products = new ProductCollection([$product1, $product2]);

        $user2 = new User1();
        $user2->name = 'doe';
        $user2->email = 'test2';
        $user2->type = UserType::TYPE2();
        $user2->date = new DateTime('2020-01-01 00:00:00');
        $user2->dateImmutable = new DateTimeImmutable('2020-01-01 10:00:00');

        $this->saveCommand->execute([$user1, $user2]);

        self::assertSame(1, $user1->id);
        self::assertSame(2, $user2->id);

        $expectedDataset = new DatasetArray([
            'user' => [
                [
                    'id' => 1,
                    'name' => 'jon',
                    'email' => 'test1',
                    'type' => 'type1',
                    'date' => null,
                    'dateImmutable' => null,
                    'types' => '["type1","type2"]',
                    'categories' => '["cat1","cat2"]',
                    'product' => '{"id":10,"ean":"test1"}',
                    'products' => '["{\"id\":10,\"ean\":\"test1\"}","{\"id\":11,\"ean\":\"test2\"}"]',
                ],
                [
                    'id' => 2,
                    'name' => 'doe',
                    'email' => 'test2',
                    'type' => 'type2',
                    'date' => '2020-01-01 00:00:00',
                    'dateImmutable' => '2020-01-01 10:00:00',
                    'types' => null,
                    'categories' => null,
                    'product' => null,
                    'products' => null,
                ],
            ]
        ]);
        $this->assertDatasetEqualsCurrent($expectedDataset);

        $query = new QueryWithIdsCommand($this->connection);
        $actual = $query->executeForOne(User1::class, 1);
        $expected = clone $user1;
        $expected->type = UserType::TYPE1();
        $expected->date = null;
        $this->assertEquals($expected, $actual);
    }

    public function testUpdateUser(): void
    {
        $user = new User1();
        $user->name = 'jon';
        $user->email = 'test1';

        $this->saveCommand->execute([$user]);

        $user = new User1();
        $user->id = 1;
        $user->name = 'test';
        $user->email = 'test2';

        $this->saveCommand->execute([$user]);

        $expectedDataset = new DatasetArray([
            'user' => [
                [
                    'id' => 1,
                    'name' => 'test',
                    'email' => 'test2',
                    'type' => 'type1',
                    'date' => null,
                    'dateImmutable' => null,
                ],
            ]
        ]);
        $this->assertDatasetEqualsCurrent($expectedDataset);
    }
}
