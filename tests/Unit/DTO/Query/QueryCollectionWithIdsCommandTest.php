<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Query;

use ArrayIterator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\Query\QueryCollectionWithIdsCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\UserDTOCollection;
use Override;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class QueryCollectionWithIdsCommandTest extends TestCase
{
    use ProphecyTrait;

    private QueryCollectionWithIdsCommand $queryCommand;
    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);
        $this->queryCommand = new QueryCollectionWithIdsCommand($this->connection->reveal());
    }

    public function testForMultipleIds(): void
    {
        $ids = [1, 3];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([
            ['id' => '1', 'name' => 'Ada', 'email' => 'ada@example.com'],
            ['id' => '3', 'name' => 'Grace', 'email' => 'grace@example.com'],
        ]));
        $this->connection
            ->executeQuery('SELECT * FROM `user` WHERE `id` in (?, ?)', $ids)
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute(UserDTOCollection::class, $ids);

        self::assertInstanceOf(UserDTOCollection::class, $actual);
        self::assertEquals([self::user(1, 'Ada', 'ada@example.com'), self::user(3, 'Grace', 'grace@example.com')], $actual->asArray());
    }

    public function testForOneIdAppendsResultsToProvidedCollection(): void
    {
        $collection = new UserDTOCollection();
        $existingUser = self::user(8, 'Existing', 'existing@example.com');
        $collection->push($existingUser);

        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([
            ['id' => '2', 'name' => 'Katherine', 'email' => 'katherine@example.com'],
        ]));
        $this->connection
            ->executeQuery('SELECT * FROM `user` WHERE `id` = ?', [2])
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute($collection, [2]);

        self::assertSame($collection, $actual);
        self::assertEquals([
            $existingUser,
            self::user(2, 'Katherine', 'katherine@example.com'),
        ], $actual->asArray());
    }

    public function testWithNoIdsDoesNotQueryAndReturnsCollection(): void
    {
        $collection = new UserDTOCollection();
        $this->connection->executeQuery(Argument::any(), Argument::any())->shouldNotBeCalled();

        $actual = $this->queryCommand->execute($collection, []);

        self::assertSame($collection, $actual);
        self::assertCount(0, $actual);
    }

    private static function user(int $id, string $name, string $email): User
    {
        $user = new User();
        $user->id = $id;
        $user->name = $name;
        $user->email = $email;

        return $user;
    }
}
