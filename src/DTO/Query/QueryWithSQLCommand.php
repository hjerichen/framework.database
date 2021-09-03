<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\DTO\DTO;

class QueryWithSQLCommand extends QueryCommandAbstract
{
    /**
     * @param string $class Should implement DTO interface.
     * @param string $sql
     * @param array<string, mixed> $parameters
     * @return DTO[]
     * @throws Exception
     */
    public function execute(string $class, string $sql, array $parameters = []): array
    {
        return $this->executeForSQL($class, $sql, $parameters);
    }
}
