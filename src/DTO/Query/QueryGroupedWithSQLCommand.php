<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\DTO\DTO;

class QueryGroupedWithSQLCommand extends QueryCommandAbstract
{
    private string $groupBy = 'id';

    public function groupBy(string $field): void
    {
        $this->groupBy = $field;
    }

    /**
     * @param string $class Should implement DTO interface.
     * @param string $sql
     * @param array<string, mixed> $parameters
     * @return DTO[][]
     * @throws Exception
     */
    public function execute(string $class, string $sql, array $parameters = []): array
    {
        return $this->executeForSQL($class, $sql, $parameters, $this->groupBy);
    }
}
