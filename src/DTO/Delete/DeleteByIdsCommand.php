<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Delete;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\DTO\DTO;
use HJerichen\FrameworkDatabase\DTO\QuoteTableColumnTrait;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverAttribute;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverBase;

class DeleteByIdsCommand
{
    use QuoteTableColumnTrait;

    private TableNameResolverAttribute $tableNameResolver;

    public function __construct(
        protected Connection $connection
    ) {
        $this->tableNameResolver = new TableNameResolverAttribute(new TableNameResolverBase());
    }

    /**
     *
     * @param class-string<DTO> $class Should implement DTO interface.
     * @param int[] $ids
     * @return int
     * @throws Exception
     */
    public function execute(string $class, array $ids): int
    {
        if (count($ids) === 0) return 0;

        $sql = $this->buildSQL($class, $ids);
        return $this->executeForSQL($sql, $ids);
    }

    private function buildSQL(string $class, array $ids): string
    {
        $tableName = $this->getTableName($class);
        return count($ids) === 1 ?
            $this->buildSQLSingle($tableName) :
            $this->buildSQLMulti($tableName, $ids);
    }

    private function getTableName(string $class): string
    {
        return $this->tableNameResolver->getTableName($class);
    }

    private function buildSQLMulti(string $tableName, array $ids): string
    {
        $tableName = $this->quoteTableName($tableName);
        $valuesString = $this->buildValuesString($ids);
        return "DELETE FROM $tableName WHERE `id` in ($valuesString)";
    }

    private function buildSQLSingle(string $tableName): string
    {
        $tableName = $this->quoteTableName($tableName);
        return "DELETE FROM $tableName WHERE `id` = ?";
    }

    private function buildValuesString(array $ids): string
    {
        $values = array_fill(start_index: 0, count: count($ids), value: '?');
        return implode(', ', $values);
    }

    /**
     * @param string $sql
     * @param int[] $ids
     * @return int
     * @throws Exception
     */
    private function executeForSQL(string $sql, array $ids = []): int
    {
        return (int)$this->connection->executeStatement($sql, $ids);
    }
}
