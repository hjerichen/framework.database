<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit;

use HJerichen\Framework\Configuration\Configuration as FrameworkConfiguration;
use HJerichen\FrameworkDatabase\Configuration;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ConfigurationTest extends TestCase
{
    use ProphecyTrait;

    private Configuration $configuration;
    /** @var ObjectProphecy<FrameworkConfiguration> */
    private ObjectProphecy $frameworkConfiguration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->frameworkConfiguration = $this->prophesize(FrameworkConfiguration::class);

        $this->configuration = new Configuration($this->frameworkConfiguration->reveal());
    }

    /* TESTS */

    public function testGetDatabaseUrl(): void
    {
        $url = 'mysql://test';
        $this->frameworkConfiguration
            ->getCustomValue('database-url')
            ->willReturn($url);

        $expected = $url;
        $actual = $this->configuration->getDatabaseUrl();
        self::assertEquals($expected, $actual);
    }

    public function testGetDatabaseUrlNull(): void
    {
        $this->frameworkConfiguration
            ->getCustomValue('database-url')
            ->willReturn(null);

        $expected = '';
        $actual = $this->configuration->getDatabaseUrl();
        self::assertEquals($expected, $actual);
    }

    public function testGetSchemaTablesProviderClass(): void
    {
        $class = MyTablesProvider::class;
        $this->frameworkConfiguration
            ->getCustomValue('database-schema-tables-provider')
            ->willReturn($class);

        $expected = $class;
        $actual = $this->configuration->getSchemaTablesProviderClass();
        self::assertEquals($expected, $actual);
    }

    public function testGetSchemaTablesProviderClassNull(): void
    {
        $this->frameworkConfiguration
            ->getCustomValue('database-schema-tables-provider')
            ->willReturn(null);

        $actual = $this->configuration->getSchemaTablesProviderClass();
        self::assertNull($actual);
    }
}