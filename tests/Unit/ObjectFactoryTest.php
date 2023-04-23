<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit;

use Doctrine\DBAL\Connection;
use Exception;
use HJerichen\Framework\Configuration\Configuration as FrameworkConfiguration;
use HJerichen\FrameworkDatabase\Configuration;
use HJerichen\FrameworkDatabase\Database\ConnectionProvider;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProviderEmpty;
use HJerichen\FrameworkDatabase\ObjectFactory;
use HJerichen\FrameworkDatabase\Test\Helpers\MyTablesProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ObjectFactoryTest extends TestCase
{
    use ProphecyTrait;
    
    private ObjectFactory $objectFactory;
    /** @var ObjectProphecy<Configuration>  */
    private ObjectProphecy $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configuration = $this->prophesize(Configuration::class);
        $configuration = $this->prophesize(FrameworkConfiguration::class);
        
        $this->objectFactory = new ObjectFactory($configuration->reveal());
    }
    
    /* TESTS */

    public function testGetDatabase(): void
    {
        $connection = $this->prophesize(Connection::class);

        $connectionProvider = new ConnectionProvider($this->configuration->reveal());
        $connectionProvider->setConnection($connection->reveal());

        $expected = $connection->reveal();
        $actual = $this->objectFactory->instantiateClass(Connection::class);
        self::assertSame($expected, $actual);
    }

    public function testGetSchemaTableProviderForNotInConfiguration(): void
    {
        $this->configuration->getSchemaTablesProviderClass()->willReturn(null);

        $expected = new TablesProviderEmpty();
        $actual = $this->objectFactory->getSchemaTableProvider($this->configuration->reveal());
        self::assertEquals($expected, $actual);
    }

    public function testGetSchemaTableProviderForClassDoesNotExist(): void
    {
        $this->configuration->getSchemaTablesProviderClass()->willReturn('something');

        $exception = new Exception('Class "something" not found.');
        $this->expectExceptionObject($exception);

        $this->objectFactory->getSchemaTableProvider($this->configuration->reveal());
    }

    public function testGetSchemaTableProviderForClassIsNotATableProvider(): void
    {
        $this->configuration->getSchemaTablesProviderClass()->willReturn(ObjectFactory::class);

        $exception = new Exception('Class "' . ObjectFactory::class . '" is not a SchemaTablesProvider.');
        $this->expectExceptionObject($exception);

        $this->objectFactory->getSchemaTableProvider($this->configuration->reveal());
    }

    public function testGetSchemaTableProviderForSetInConfiguration(): void
    {
        $this->configuration->getSchemaTablesProviderClass()->willReturn(MyTablesProvider::class);

        $expected = new MyTablesProvider();
        $actual = $this->objectFactory->getSchemaTableProvider($this->configuration->reveal());
        self::assertEquals($expected, $actual);
    }
}