<?php

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use HJerichen\FrameworkDatabase\Database\Schema\Table;

class UserTable2 implements Table
{
    /** @throws SchemaException */
    public function addToSchema(Schema $schema): void
    {
        $table = $schema->createTable('user');
        $table->addColumn('id', Types::INTEGER,[
            'unsigned' => true,
            'autoincrement' => true
        ]);
        $table->addColumn('email', Types::STRING, [
            'notnull' => true
        ]);
        $table->addColumn('date', Types::DATETIME_MUTABLE, [
            'notnull' => false
        ]);
        $table->addColumn('dateImmutable', Types::DATETIME_IMMUTABLE, [
            'notnull' => false
        ]);
        $table->addColumn('types', Types::JSON, [
            'notnull' => false
        ]);
        $table->addColumn('categories', Types::JSON, [
            'notnull' => false
        ]);
        $table->setPrimaryKey(['id']);
    }
}