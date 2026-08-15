<?php

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Table as TableSchema;
use Doctrine\DBAL\Types\Types;
use HJerichen\FrameworkDatabase\Database\Schema\Table;
use Override;

class UserTable1 implements Table
{
    #[Override]
    public function getSchema(): TableSchema
    {
        return TableSchema::editor()
            ->setUnquotedName('user')
            ->addColumn(
                Column::editor()
                    ->setUnquotedName('id')
                    ->setTypeName(Types::INTEGER)
                    ->setUnsigned(true)
                    ->setAutoincrement(true)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('email')
                    ->setTypeName(Types::STRING)
                    ->setLength(255)
                    ->setNotNull(true)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('name')
                    ->setTypeName(Types::STRING)
                    ->setLength(255)
                    ->setNotNull(true)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('date')
                    ->setTypeName(Types::DATETIME_MUTABLE)
                    ->setNotNull(false)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('dateImmutable')
                    ->setTypeName(Types::DATETIME_IMMUTABLE)
                    ->setNotNull(false)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('type')
                    ->setTypeName(Types::STRING)
                    ->setLength(10)
                    ->setDefaultValue(UserType::TYPE1->value)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('types')
                    ->setTypeName(Types::JSON)
                    ->setNotNull(false)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('categories')
                    ->setTypeName(Types::JSON)
                    ->setNotNull(false)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('product')
                    ->setTypeName(Types::JSON)
                    ->setNotNull(false)
                    ->create()
            )->addColumn(
                Column::editor()
                    ->setUnquotedName('products')
                    ->setTypeName(Types::JSON)
                    ->setNotNull(false)
                    ->create()
            )->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()
                    ->setUnquotedColumnNames(firstColumnName: 'id')
                    ->create()
            )->create();
    }
}