<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\Database\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTable1;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTable2;
use Override;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SchemaSynchronizerTest extends TestCase
{
    use ProphecyTrait;

    private SchemaSynchronizer $schemaSynchronizer;
    /** @var ObjectProphecy<SchemaProvider>  */
    private ObjectProphecy $schemaProvider;
    /** @var ObjectProphecy<Connection>  */
    private ObjectProphecy $connection;

    #[Override]
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
        $schemaWanted = new Schema([(new UserTable1())->getSchema()]);
        $schemaCurrent = new Schema([(new UserTable1())->getSchema()]);

        $this->schemaProvider->getWantedSchema()->willReturn($schemaWanted);
        $this->schemaProvider->getCurrentSchema()->willReturn($schemaCurrent);

        $this->connection->executeQuery(Argument::any())->shouldNotBeCalled();

        $this->schemaSynchronizer->execute();
    }

    public function testForChanges(): void
    {
        $result = $this->prophesize(Result::class);

        $schemaWanted = new Schema([(new UserTable1())->getSchema()]);
        $schemaCurrent = new Schema([(new UserTable2())->getSchema()]);

        $this->schemaProvider->getWantedSchema()->willReturn($schemaWanted);
        $this->schemaProvider->getCurrentSchema()->willReturn($schemaCurrent);

        $expected = "ALTER TABLE user ADD name VARCHAR(255) NOT NULL, ADD type VARCHAR(10) DEFAULT 'type1' NOT NULL";
        $this->connection->executeQuery($expected)->shouldBeCalledOnce()->willReturn($result->reveal());

        $this->schemaSynchronizer->execute();
    }

    public function testForChanges_withOtherPlatform(): void
    {
        $result = $this->prophesize(Result::class);

        $schemaWanted = new Schema([(new UserTable1())->getSchema()]);
        $schemaCurrent = new Schema([(new UserTable2())->getSchema()]);

        $this->schemaProvider->getWantedSchema()->willReturn($schemaWanted);
        $this->schemaProvider->getCurrentSchema()->willReturn($schemaCurrent);

        $expected = "ALTER TABLE \"user\" ADD name VARCHAR(255) NOT NULL";
        $this->connection->executeQuery($expected)->shouldBeCalledOnce()->willReturn($result);

        $expected = "ALTER TABLE \"user\" ADD type VARCHAR(10) DEFAULT 'type1' NOT NULL";
        $this->connection->executeQuery($expected)->shouldBeCalledOnce()->willReturn($result);

        $this->schemaSynchronizer->setPlatform(new PostgreSQLPlatform());
        $this->schemaSynchronizer->execute();
    }
}
