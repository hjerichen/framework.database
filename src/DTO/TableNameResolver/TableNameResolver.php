<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\TableNameResolver;

use HJerichen\FrameworkDatabase\DTO\DTO;

interface TableNameResolver
{
    public function getTableName(DTO|string $objectOrClass): string;
}
