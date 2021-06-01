<?php

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use HJerichen\FrameworkDatabase\Database\Schema\Table;

class UserTable1 implements Table
{
    /** @throws SchemaException */
    public function addToSchema(Schema $schema): void
    {
        $table = $schema->createTable('user');
        $table->addColumn('id', 'integer',[
            'unsigned' => true,
            'autoincrement' => true
        ]);
        $table->addColumn('email', 'string');
        $table->addColumn('name', 'string');
        $table->addColumn('type', 'string');
        $table->setPrimaryKey(['id']);
    }
}