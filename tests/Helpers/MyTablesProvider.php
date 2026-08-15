<?php

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\FrameworkDatabase\Database\Schema\Table;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use Override;

class MyTablesProvider implements TablesProvider
{
    /** @return Table[] */
    #[Override]
    public function getSchemaTables(): array
    {
        return [new UserTable1()];
    }
}