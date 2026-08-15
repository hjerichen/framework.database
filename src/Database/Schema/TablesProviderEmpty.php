<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

use Override;

class TablesProviderEmpty implements TablesProvider
{
    /** @return Table[] */
    #[Override]
    public function getSchemaTables(): array
    {
        return [];
    }
}