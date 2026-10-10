<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Query;

use Doctrine\DBAL\Exception;
use HJerichen\FrameworkDatabase\DTO\DTO;
use HJerichen\FrameworkDatabase\DTO\DTOCollection;

class QueryCollectionWithFieldFilterCommand extends QueryCommandAbstract
{
    /**
     * @template T of DTO
     * @param DTOCollection<T>|class-string<DTOCollection<T>> $collection
     * @param array<string,mixed> $fieldFilter
     * @return DTOCollection<T>
     * @throws Exception
     */
    public function execute(DTOCollection|string $collection, array $fieldFilter = []): DTOCollection
    {
        if (is_string($collection)) {
            /** @psalm-suppress UnsafeInstantiation The caller supplies a concrete no-argument DTOCollection class. */
            $collection = new $collection();
        }

        $command = new QueryWithFieldFilterCommand($this->connection);
        $result = $command->execute($collection->getType(), $fieldFilter);
        $collection->pushMultiple($result);

        return $collection;
    }
}
