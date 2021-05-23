<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\TableNameResolver;

use HJerichen\FrameworkDatabase\DTO\DTO;

class TableNameResolverBase implements TableNameResolver
{
    public function getTableName(DTO|string $objectOrClass): string
    {
        $class = ($objectOrClass instanceof DTO) ? $objectOrClass::class : $objectOrClass;
        $exploded = explode('\\', $class);
        return lcfirst(end($exploded));
    }
}
