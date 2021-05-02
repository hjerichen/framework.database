<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

class TablesProviderEmpty implements TablesProvider
{
    public function getSchemaTables(): array
    {
        return [];
    }
}