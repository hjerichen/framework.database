<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration\DTO;

use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\DTO\Delete\DeleteByIdsCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;

class DeleteByIdsCommandTest extends DatabaseTestCase
{
    private DeleteByIdsCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new DeleteByIdsCommand($this->connection);
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

    public function testDeleteUsers(): void
    {
        $this->command->execute(User::class, [1, 3]);

        $this->assertDatasetEqualsCurrent(new DatasetArray([
            'user' => [
                ['id' => 2, 'name' => 'doe', 'email' => 'test2@test.de'],
            ]
        ]));
    }
}
