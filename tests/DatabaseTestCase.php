<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test;

use Doctrine\DBAL\Connection;
use HJerichen\DBUnit\Comparator\DatabaseDatasetComparator;
use HJerichen\DBUnit\Dataset\Database\DatabaseDatasetPDO;
use HJerichen\DBUnit\Dataset\Dataset;
use HJerichen\DBUnit\Dataset\DatasetArray;
use HJerichen\DBUnit\MySQLTestCaseTrait;
use HJerichen\FrameworkDatabase\Configuration;
use HJerichen\FrameworkDatabase\Database\ConnectionProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProviderEmpty;
use PDO;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;
use SebastianBergmann\Comparator\ComparisonFailure;

class DatabaseTestCase extends TestCase
{
    use ProphecyTrait;
    use MySQLTestCaseTrait {
        MySQLTestCaseTrait::setUp as private setUpDatabase;
    }

    protected Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $connectionProvider = new ConnectionProvider($this->getConfiguration());
        $this->connection = $connectionProvider->getConnection();

        $this->setUpSchema();
        $this->setUpDatabase();
    }

    /**
     * @noinspection PhpUndefinedConstantInspection
     * @psalm-suppress UndefinedConstant
     */
    protected function getConfiguration(): Configuration
    {
        $configuration = $this->prophesize(Configuration::class);
        $configuration->getDatabaseUrl()->willReturn(MYSQL_URL);
        return $configuration->reveal();
    }

    protected function getDatabase(): PDO
    {
        $database = $this->connection->getNativeConnection();
        if ($database instanceof PDO) return $database;

        throw new RuntimeException('Only PDO as connection supported');
    }

    protected function getDatasetForSetup(): Dataset
    {
        return new DatasetArray([]);
    }

    protected function setUpSchema(): void
    {
        $tablesProvider = $this->getSchemaTablesProvider();
        $schemaProvider = new SchemaProvider($this->connection, $tablesProvider);
        $schemaSynchronizer = new SchemaSynchronizer($this->connection, $schemaProvider);
        $schemaSynchronizer->execute();
    }

    protected function getSchemaTablesProvider(): TablesProvider
    {
        return new TablesProviderEmpty();
    }

    /**
     * @psalm-suppress InternalClass
     * @psalm-suppress InternalMethod
     */
    protected function assertDatasetEqualsCurrent(Dataset $expected): void
    {
        try {
            $database = $this->getDatabase();
            $actual = new DatabaseDatasetPDO($database);

            $comparator = new DatabaseDatasetComparator();
            $comparator->assertEquals($expected, $actual);
            self::assertTrue(true);
        } catch (ComparisonFailure $failure) {
            throw new ExpectationFailedException($failure->getMessage(), $failure);
        }
    }
}