<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\DTO\DTO;

class QueryWithFieldFilterCommand extends QueryCommandAbstract
{
    /**
     * @template T of DTO
     * @param class-string<T> $class Should implement DTO interface.
     * @param array<string, mixed> $fieldFilter
     * @return T[]
     * @throws Exception
     */
    public function execute(string $class, array $fieldFilter = []): array
    {
        $sql = $this->buildQuery($class, $fieldFilter);
        return $this->executeForSQL($class, $sql, $fieldFilter);
    }

    /** @param array<string, mixed> $fieldFilter */
    private function buildQuery(string $class, array $fieldFilter): string
    {
        $tableName = $this->getTableName($class);
        $wheres = $this->buildWheres($fieldFilter);

        $sql = "SELECT * FROM {$this->quoteTableName($tableName)}";
        if (count($wheres) === 0) return $sql;

        return "$sql WHERE " . implode(' AND ', $wheres);
    }

    /**
     * @param array<string, mixed> $parameters
     * @return list<string>
     */
    private function buildWheres(array $parameters): array
    {
        $wheres = [];
        foreach (array_keys($parameters) as $key) {
            $wheres[] = "{$this->quoteColumnName($key)} = :$key";
        }
        return $wheres;
    }
}
