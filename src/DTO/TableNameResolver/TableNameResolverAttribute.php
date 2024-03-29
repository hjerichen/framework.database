<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\TableNameResolver;

use HJerichen\FrameworkDatabase\DTO\Attributes\Table;
use HJerichen\FrameworkDatabase\DTO\DTO;
use ReflectionClass;

class TableNameResolverAttribute implements TableNameResolver
{
    public function __construct(
        private readonly TableNameResolver $tableNameResolver
    ) {
    }

    /** @param DTO|class-string $objectOrClass */
    public function getTableName(DTO|string $objectOrClass): string
    {
        $tableAttribute = $this->getAttribute($objectOrClass);
        if ($tableAttribute) return $tableAttribute->name;

        return $this->tableNameResolver->getTableName($objectOrClass);
    }

    /**
     * @param DTO|class-string $object
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpDocSignatureIsNotCompleteInspection
     */
    private function getAttribute(DTO|string $object): ?Table
    {
        $class = new ReflectionClass($object);
        $attribute = $class->getAttributes(Table::class)[0] ?? null;

        return $attribute ? $attribute->newInstance() : null;
    }
}
