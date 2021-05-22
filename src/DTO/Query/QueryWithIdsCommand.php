<?php
/**
 * @noinspection SqlResolve
 * @noinspection UnknownInspectionInspection
 */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

class QueryWithIdsCommand extends QueryCommandAbstract
{
    public function execute(string $class, array $ids): array
    {
        if (count($ids) === 0) return [];

        $sql = $this->buildSQL($class, $ids);
        return $this->executeForSQL($class, $sql, $ids);
    }

    public function executeForOne(string $class, int $id): ?object
    {
        $sql = $this->buildSQL($class, [$id]);
        $result = $this->executeForSQL($class, $sql, [$id]);
        return $result[0] ?? null;
    }

    private function buildSQL(string $class, array $ids): string
    {
        $tableName = $this->getTableName($class);
        return count($ids) === 1 ?
            $this->buildSQLSingle($tableName) :
            $this->buildSQLMulti($tableName, $ids);
    }

    private function buildSQLMulti(string $tableName, array $ids): string
    {
        $tableName = $this->quoteTableName($tableName);
        $valuesString = $this->buildValuesString($ids);
        return "SELECT * FROM $tableName WHERE `id` in ($valuesString)";
    }

    private function buildSQLSingle(string $tableName): string
    {
        $tableName = $this->quoteTableName($tableName);
        return "SELECT * FROM $tableName WHERE `id` = ?";
    }

    private function buildValuesString(array $ids): string
    {
        $values = array_map(static fn() => '?', $ids);
        return implode(', ', $values);
    }
}
