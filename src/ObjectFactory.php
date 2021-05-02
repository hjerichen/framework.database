<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\Database\ConnectionProvider;
use HJerichen\Framework\ObjectFactory as FrameworkObjectFactory;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProviderEmpty;

class ObjectFactory extends FrameworkObjectFactory
{
    /** @throws Exception */
    public function getDatabase(): Connection
    {
        $provider = $this->instantiateClass(ConnectionProvider::class);
        return $provider->getConnection();
    }

    public function getSchemaTableProvider(Configuration $configuration): TablesProvider
    {
        $class = $configuration->getSchemaTablesProviderClass() ?? TablesProviderEmpty::class;

        $provider = $this->instantiateClass($class);
        if ($provider instanceof TablesProvider) return $provider;

        $message = "Class \"$class\" is not a SchemaTablesProvider.";
        throw new \Exception($message);
    }
}