<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\QuoteTableColumnTrait;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolver;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverAttribute;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverBase;
use HJerichen\FrameworkDatabase\DTO\Utils;

abstract class QueryCommandAbstract
{
    use QuoteTableColumnTrait {
        quoteTableName as protected;
        quoteColumnName as protected;
    }

    private TableNameResolver $tableNameResolver;

    public function __construct(
        protected Connection $connection
    ) {
        $this->tableNameResolver = new TableNameResolverAttribute(new TableNameResolverBase());
    }

    /**
     * @param string $class
     * @param string $sql
     * @param array<string, mixed> $parameters
     * @return array
     * @throws Exception
     */
    protected function executeForSQL(string $class, string $sql, array $parameters = []): array
    {
        $result = $this->connection->executeQuery($sql, $parameters);
        return $this->buildDTOs($class, $result);
    }

    protected function getTableName(string $class): string
    {
        return $this->tableNameResolver->getTableName($class);
    }

    private function buildDTOs(string $class, Result $result): array
    {
        $objects = [];
        foreach ($result->iterateAssociative() as $data) {
            $object = new $class;
            $objects[] = $object;
            Utils::populateObject($object, $data);
        }
        return $objects;
    }
}
