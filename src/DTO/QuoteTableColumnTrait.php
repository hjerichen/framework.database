<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

trait QuoteTableColumnTrait
{
    private function quoteTableName(string $tableName): string
    {
        return "`$tableName`";
    }

    private function quoteColumnName(string $columnName): string
    {
        return "`$columnName`";
    }
}
