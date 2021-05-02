<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\Database\Schema;

use HJerichen\FrameworkDatabase\Database\Schema\TablesProviderEmpty;
use PHPUnit\Framework\TestCase;

class TablesProviderEmptyTest extends TestCase
{
    public function testGetSchemaTablesIsEmptyArray(): void
    {
        $provider = new TablesProviderEmpty();

        $expected = [];
        $actual = $provider->getSchemaTables();
        self::assertEquals($expected, $actual);
    }
}
