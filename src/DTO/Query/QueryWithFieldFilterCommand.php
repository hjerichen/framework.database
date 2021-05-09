<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;

class QueryWithFieldFilterCommand extends QueryCommandAbstract
{
    /**
     * @param string $class
     * @param array<string, mixed> $fieldFilter
     * @return array
     * @throws Exception
     */
    public function execute(string $class, array $fieldFilter = []): array
    {
        $sql = $this->buildQuery($class, $fieldFilter);
        return $this->executeForSQL($class, $sql, $fieldFilter);
    }

    private function buildQuery(string $class, array $fieldFilter): string
    {
        $tableName = $this->getTableName($class);
        $wheres = $this->buildWheres($fieldFilter);

        $sql = "SELECT * FROM $tableName";
        if (count($wheres) === 0) return $sql;

        return "$sql WHERE " . implode(' AND ', $wheres);
    }

    private function buildWheres(array $parameters): array
    {
        $wheres = [];
        foreach ($parameters as $key => $value) {
            $wheres[] = "$key = :$key";
        }
        return $wheres;
    }
}
