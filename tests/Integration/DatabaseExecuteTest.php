<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration;

use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use HJerichen\FrameworkDatabase\Testing\DatabaseTestCase;
use Override;

class DatabaseExecuteTest extends DatabaseTestCase
{
    #[Override]
    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new MyTablesProvider();
    }


    /* TESTS */

    public function testExecute(): void
    {
        $this->connection->insert('user', ['email' => 'test@test.de', 'name' => 'test']);

        $expected = new DatasetArray([
            'user' => [
                ['email' => 'test@test.de', 'name' => 'test']
            ]
        ]);
        $this->assertDatasetEqualsCurrent($expected);
    }
}