<?php
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\DTO\DTOCollection;
use HJerichen\FrameworkDatabase\DTO\DTO;

class QueryCollectionWithSQLCommand extends QueryCommandAbstract
{
    /**
     * @template T of DTO
     * @param DTOCollection<T>|class-string<DTOCollection<T>> $collection
     * @param string $sql
     * @param array<string, mixed> $parameters
     * @return DTOCollection<T>
     * @throws Exception
     */
    public function execute(DTOCollection|string $collection, string $sql, array $parameters = []): DTOCollection
    {
        if (is_string($collection)) {
            /** @psalm-suppress UnsafeInstantiation The caller supplies a concrete no-argument DTOCollection class. */
            $collection = new $collection();
        }
        $result = $this->executeForSQL($collection->getType(), $sql, $parameters);
        $collection->pushMultiple($result);
        return $collection;
    }
}