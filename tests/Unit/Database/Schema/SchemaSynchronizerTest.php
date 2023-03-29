<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTable1;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTable2;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SchemaSynchronizerTest extends TestCase
{
    use ProphecyTrait;

    private SchemaSynchronizer $schemaSynchronizer;
    private ObjectProphecy|SchemaProvider $schemaProvider;
    private ObjectProphecy|Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaProvider = $this->prophesize(SchemaProvider::class);
        $this->connection = $this->prophesize(Connection::class);

        $this->schemaSynchronizer = new SchemaSynchronizer(
            $this->connection->reveal(),
            $this->schemaProvider->reveal()
        );
    }

    /* TESTS */

    public function testForNoChanges(): void
    {
        $schemaWanted = new Schema();
        $schemaCurrent = new Schema();
        (new UserTable1())->addToSchema($schemaWanted);
        (new UserTable1())->addToSchema($schemaCurrent);

        $this->schemaProvider->getWantedSchema()->willReturn($schemaWanted);
        $this->schemaProvider->getCurrentSchema()->willReturn($schemaCurrent);

        $this->connection->executeQuery(Argument::any())->shouldNotBeCalled();

        $this->schemaSynchronizer->execute();
    }

    public function testForChanges(): void
    {
        $schemaWanted = new Schema();
        $schemaCurrent = new Schema();
        (new UserTable1())->addToSchema($schemaWanted);
        (new UserTable2())->addToSchema($schemaCurrent);

        $this->schemaProvider->getWantedSchema()->willReturn($schemaWanted);
        $this->schemaProvider->getCurrentSchema()->willReturn($schemaCurrent);

        $expected = "ALTER TABLE user ADD name VARCHAR(255) NOT NULL, ADD type VARCHAR(255) DEFAULT 'type1' NOT NULL";
        $this->connection->executeQuery($expected)->shouldBeCalledOnce();

        $this->schemaSynchronizer->execute();
    }

    public function testForChanges_withOtherPlatform(): void
    {
        $schemaWanted = new Schema();
        $schemaCurrent = new Schema();
        (new UserTable1())->addToSchema($schemaWanted);
        (new UserTable2())->addToSchema($schemaCurrent);

        $this->schemaProvider->getWantedSchema()->willReturn($schemaWanted);
        $this->schemaProvider->getCurrentSchema()->willReturn($schemaCurrent);

        $expected = "ALTER TABLE \"user\" ADD name VARCHAR(255) NOT NULL";
        $this->connection->executeQuery($expected)->shouldBeCalledOnce();

        $expected = "ALTER TABLE \"user\" ADD type VARCHAR(255) DEFAULT 'type1' NOT NULL";
        $this->connection->executeQuery($expected)->shouldBeCalledOnce();

        $this->schemaSynchronizer->setPlatform(new PostgreSQLPlatform());
        $this->schemaSynchronizer->execute();
    }
}
