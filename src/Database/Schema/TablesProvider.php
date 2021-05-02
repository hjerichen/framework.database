<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

use HJerichen\ClassInstantiator\Attribute\Instantiator;
use HJerichen\FrameworkDatabase\ObjectFactory;

#[Instantiator(ObjectFactory::class)]
interface TablesProvider
{
    /** @return Table[] */
    public function getSchemaTables(): array;
}