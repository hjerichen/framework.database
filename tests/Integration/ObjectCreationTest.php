<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Integration;

use HJerichen\Framework\Configuration\Configuration;
use HJerichen\Framework\ObjectFactory;
use HJerichen\FrameworkDatabase\CLIController;
use HJerichen\FrameworkDatabase\Database\ConnectionProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ObjectCreationTest extends TestCase
{
    use ProphecyTrait;

    public function testObjectCreations(): void
    {
        $classes = [
            ConnectionProvider::class,
            SchemaSynchronizer::class,
            SchemaProvider::class,
            TablesProvider::class,
            CLIController::class
        ];

        $configuration = $this->prophesize(Configuration::class);
        $objectFactory = new ObjectFactory($configuration->reveal());
        foreach ($classes as $class) {
            $object = $objectFactory->instantiateClass($class);
            self::assertInstanceOf($class, $object);
        }
    }
}
