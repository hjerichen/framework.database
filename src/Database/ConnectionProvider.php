<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use HJerichen\ClassInstantiator\Attribute\Instantiator;
use HJerichen\FrameworkDatabase\Configuration;
use HJerichen\FrameworkDatabase\ObjectFactory;

#[Instantiator(ObjectFactory::class)]
class ConnectionProvider
{
    private static Connection|null $connection = null;

    public function __construct(
        private Configuration $configuration
    ) {
    }

    /**  @throws Exception */
    public function getConnection(): Connection
    {
        if (self::$connection === null) {
            $connectionParams = array(
                'url' => $this->configuration->getDatabaseUrl()
            );
            self::$connection = DriverManager::getConnection($connectionParams);
            self::$connection->connect();
        }
        return self::$connection;
    }

    public function setConnection(Connection|null $connection): void
    {
        if (self::$connection) {
            self::$connection->close();
        }
        self::$connection = $connection;
    }
}