<?php  declare(strict_types=1);
/** @noinspection PhpUnhandledExceptionInspection */

namespace HJerichen\FrameworkDatabase\Tests\Integration;

use Doctrine\DBAL\Connection;
use HJerichen\FrameworkDatabase\Configuration;
use HJerichen\FrameworkDatabase\Database\ConnectionProvider;
use PDO;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DatabaseConnectionTest extends TestCase
{
    use ProphecyTrait;

    private ConnectionProvider $connectionProvider;
    private Connection $connection;

    /** @noinspection PhpUndefinedConstantInspection */
    protected function setUp(): void
    {
        parent::setUp();
        $configuration = $this->prophesize(Configuration::class);
        $configuration->getDatabaseUrl()->willReturn(MYSQL_URL);

        $this->connectionProvider = new ConnectionProvider($configuration->reveal());
        $this->connection = $this->connectionProvider->getConnection();
    }

    protected function tearDown(): void
    {
        $this->connectionProvider->setConnection(null);
        self::assertFalse($this->connection->isConnected());
    }

    /* TESTS */

    public function testConnection(): void
    {
        self::assertTrue($this->connection->isConnected());
    }

    public function testFetchingPDOInstance(): void
    {
        $actual = $this->connection->getNativeConnection();
        self::assertInstanceOf(PDO::class, $actual);
    }
}