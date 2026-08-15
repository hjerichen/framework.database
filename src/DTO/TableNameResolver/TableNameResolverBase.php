<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\TableNameResolver;

use HJerichen\FrameworkDatabase\DTO\DTO;
use Override;

class TableNameResolverBase implements TableNameResolver
{
    #[Override]
    public function getTableName(DTO|string $objectOrClass): string
    {
        $class = ($objectOrClass instanceof DTO) ? $objectOrClass::class : $objectOrClass;
        $exploded = explode('\\', $class);
        return lcfirst(end($exploded));
    }
}
