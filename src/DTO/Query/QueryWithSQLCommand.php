<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;

class QueryWithSQLCommand extends QueryCommandAbstract
{
    /**
     * @param string $class
     * @param string $sql
     * @param array<string, mixed> $parameters
     * @return array
     * @throws Exception
     */
    public function execute(string $class, string $sql, array $parameters = []): array
    {
        return $this->executeForSQL($class, $sql, $parameters);
    }
}
