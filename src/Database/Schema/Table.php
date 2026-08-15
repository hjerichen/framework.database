<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

use Doctrine\DBAL\Schema\Table as TableSchema;

interface Table
{
    public function getSchema(): TableSchema;
}