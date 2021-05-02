<?php

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\FrameworkDatabase\Database\Schema\Table;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;

class MyTablesProvider implements TablesProvider
{
    /** @return Table[] */
    public function getSchemaTables(): array
    {
        return [new UserTable1()];
    }
}