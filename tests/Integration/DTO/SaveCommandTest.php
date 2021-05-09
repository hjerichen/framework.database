<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Save\SaveCommand;
use HJerichen\FrameworkDatabase\Test\DatabaseTestCase;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;

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
        $user1 = new User();
        $user1->name = 'jon';
        $user1->email = 'test1';

        $user2 = new User();
        $user2->name = 'doe';
        $user2->email = 'test2';

        $this->saveCommand->execute([$user1, $user2]);

        self::assertSame(1, $user1->id);
        self::assertSame(2, $user2->id);

        $expectedDataset = new DatasetArray([
            'user' => [
                ['id' => 1, 'name' => 'jon', 'email' => 'test1'],
                ['id' => 2, 'name' => 'doe', 'email' => 'test2'],
            ]
        ]);
        $this->assertDatasetEqualsCurrent($expectedDataset);
    }
}
