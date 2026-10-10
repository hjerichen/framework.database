<?php
/** @noinspection PhpRedundantOptionalArgumentInspection */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Query;

use ArrayIterator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\Query\QueryCollectionWithSQLCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\UserDTOCollection;
use Override;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class QueryCollectionWithSQLCommandTest extends TestCase
{
    use ProphecyTrait;

    private QueryCollectionWithSQLCommand $queryCommand;
    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);
        $this->queryCommand = new QueryCollectionWithSQLCommand($this->connection->reveal());
    }

    public function testExecuteWithCollectionInstanceAppendsHydratedDTOs(): void
    {
        $existingUser = new User();
        $existingUser->id = 1;
        $existingUser->name = 'existing';
        $existingUser->email = 'existing@example.com';
        $collection = new UserDTOCollection();
        $collection->push($existingUser);

        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([
            ['id' => '2', 'name' => 'Ada', 'email' => 'ada@example.com'],
        ]));
        $this->connection
            ->executeQuery('SELECT * FROM user WHERE id = :id', ['id' => 2])
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute(
            $collection,
            'SELECT * FROM user WHERE id = :id',
            ['id' => 2],
        );

        self::assertSame($collection, $actual);
        self::assertCount(2, $actual);
        self::assertSame($existingUser, $actual[0]);

        $expectedUser = new User();
        $expectedUser->id = 2;
        $expectedUser->name = 'Ada';
        $expectedUser->email = 'ada@example.com';
        self::assertEquals($expectedUser, $actual[1]);
    }

    public function testExecuteWithCollectionClassCreatesAndPopulatesCollection(): void
    {
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([
            ['id' => '3', 'name' => 'Grace', 'email' => 'grace@example.com'],
        ]));
        $this->connection
            ->executeQuery('SELECT * FROM user', [])
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute(UserDTOCollection::class, 'SELECT * FROM user');

        self::assertInstanceOf(UserDTOCollection::class, $actual);
        self::assertCount(1, $actual);

        $expectedUser = new User();
        $expectedUser->id = 3;
        $expectedUser->name = 'Grace';
        $expectedUser->email = 'grace@example.com';
        self::assertEquals($expectedUser, $actual[0]);
    }

    public function testExecuteWithEmptyResultKeepsExistingCollectionItems(): void
    {
        $existingUser = new User();
        $existingUser->id = 1;
        $existingUser->name = 'existing';
        $existingUser->email = 'existing@example.com';
        $collection = new UserDTOCollection();
        $collection->push($existingUser);

        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([]));
        $this->connection
            ->executeQuery('SELECT * FROM user WHERE id = :id', ['id' => 99])
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute(
            $collection,
            'SELECT * FROM user WHERE id = :id',
            ['id' => 99],
        );

        self::assertSame($collection, $actual);
        self::assertSame([$existingUser], $actual->asArray());
    }
}
