<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use HJerichen\Framework\Response\TextResponse;
use HJerichen\FrameworkDatabase\CLIController;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class CLIControllerTest extends TestCase
{
    use ProphecyTrait;

    private CLIController $controller;
    /** @var ObjectProphecy<SchemaSynchronizer> */
    private ObjectProphecy $schemaSynchronizer;
    /** @var ObjectProphecy<SchemaProvider> */
    private ObjectProphecy $schemaProvider;
    /** @var ObjectProphecy<Schema> */
    private ObjectProphecy $schema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaSynchronizer = $this->prophesize(SchemaSynchronizer::class);
        $this->schemaProvider = $this->prophesize(SchemaProvider::class);
        $this->schema = $this->prophesize(Schema::class);

        $this->controller = new CLIController();
    }

    /* TESTS */

    public function testPrintWantedSchema(): void
    {
        $queries = ['query1', 'query2'];

        $this->schema
            ->toSql(new MySQLPlatform)
            ->willReturn($queries);
        $this->schemaProvider
            ->getWantedSchema()
            ->willReturn($this->schema->reveal());

        $expected = new TextResponse("query1\nquery2\n");
        $actual = $this->controller->printWantedSchema($this->schemaProvider->reveal());
        self::assertEquals($expected, $actual);
    }

    public function testPrintCurrentSchema(): void
    {
        $queries = ['query1', 'query2'];

        $this->schema
            ->toSql(new MySQLPlatform)
            ->willReturn($queries);
        $this->schemaProvider
            ->getCurrentSchema()
            ->willReturn($this->schema->reveal());

        $expected = new TextResponse("query1\nquery2\n");
        $actual = $this->controller->printCurrentSchema($this->schemaProvider->reveal());
        self::assertEquals($expected, $actual);
    }

    public function testPrintUpdateSchema(): void
    {
        $queries = ['query1', 'query2'];
        $this->schemaSynchronizer->calculateQueries()->willReturn($queries);

        $expected = new TextResponse("query1\nquery2\n");
        $actual = $this->controller->printUpdateSchema($this->schemaSynchronizer->reveal());
        self::assertEquals($expected, $actual);
    }

    public function testUpdateSchema(): void
    {
        $queries = ['query1', 'query2'];
        $this->schemaSynchronizer->execute()->willReturn($queries);

        $expected = new TextResponse("query1\nquery2\n");
        $actual = $this->controller->updateSchema($this->schemaSynchronizer->reveal());
        self::assertEquals($expected, $actual);
    }
}
