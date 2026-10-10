<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Query;

use ArrayIterator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\Query\QueryCollectionWithFieldFilterCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\UserDTOCollection;
use Override;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class QueryCollectionWithFieldFilterCommandTest extends TestCase
{
    use ProphecyTrait;

    private QueryCollectionWithFieldFilterCommand $queryCommand;
    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);
        $this->queryCommand = new QueryCollectionWithFieldFilterCommand($this->connection->reveal());
    }

    public function testWithEmptyFilter(): void
    {
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([
            ['id' => '1', 'name' => 'Ada', 'email' => 'ada@example.com'],
        ]));
        $this->connection
            ->executeQuery('SELECT * FROM `user`', [])
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute(UserDTOCollection::class);

        self::assertEquals([self::user(1, 'Ada', 'ada@example.com')], $actual->asArray());
    }

    public function testWithFieldFilter(): void
    {
        $filter = ['name' => 'Grace', 'email' => 'grace@example.com'];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([
            ['id' => '2', 'name' => 'Grace', 'email' => 'grace@example.com'],
        ]));
        $this->connection
            ->executeQuery(
                'SELECT * FROM `user` WHERE `name` = :name AND `email` = :email',
                $filter,
            )
            ->willReturn($result->reveal())
            ->shouldBeCalledOnce();

        $actual = $this->queryCommand->execute(new UserDTOCollection(), $filter);

        self::assertEquals([self::user(2, 'Grace', 'grace@example.com')], $actual->asArray());
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
