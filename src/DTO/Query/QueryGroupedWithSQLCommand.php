<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\DTO;

class QueryGroupedWithSQLCommand extends QueryCommandAbstract
{
    private string $groupBy = 'id';

    public function groupBy(string $field): void
    {
        $this->groupBy = $field;
    }

    /**
     * @template T of DTO
     * @param class-string<T> $class Should implement DTO interface.
     * @param string $sql
     * @param array<string, mixed> $parameters
     * @return T[][]
     * @throws Exception
     */
    public function execute(string $class, string $sql, array $parameters = []): array
    {
        $result = $this->connection->executeQuery($sql, $parameters);
        return $this->buildDTOs($class, $result);
    }

    /**
     * @template T of DTO
     * @param class-string<T> $class
     * @param Result $result
     * @return T[][]
     * @throws Exception
     */
    private function buildDTOs(string $class, Result $result): array
    {
        $objects = [];
        foreach ($result->iterateAssociative() as $data) {
            /** @var string $group */
            $group = $data[$this->groupBy] ?? '';
            $objects[$group][] = $this->buildDTO($class, $data);
        }
        return $objects;
    }
}
