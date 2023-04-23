<?php
/** @noinspection DuplicatedCode */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Query;

use ArrayIterator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithIdsCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\User1;
use HJerichen\FrameworkDatabase\Test\Helpers\UserType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class QueryWithIdsCommandTest extends TestCase
{
    use  ProphecyTrait;

    private QueryWithIdsCommand $queryCommand;
    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);

        $this->queryCommand = new QueryWithIdsCommand($this->connection->reveal());
    }

    /* TESTS */

    public function testWithNoIds(): void
    {
        $this->connection->executeQuery(Argument::any())->shouldNotBeCalled();

        $expected = [];
        $actual = $this->queryCommand->execute(User::class, []);
        self::assertEquals($expected, $actual);
    }

    public function testForMultipleIds(): void
    {
        $ids = [1, 4, 7];

        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test1@test.de';

        $user2 = new User();
        $user2->id = 7;
        $user2->name = 'doe';
        $user2->email = 'test3@test.de';

        $results = [
            ['id' => '1', 'name' => 'jon', 'email' => 'test1@test.de'],
            ['id' => '7', 'name' => 'doe', 'email' => 'test3@test.de'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $expectedSQL = 'SELECT * FROM `user` WHERE `id` in (?, ?, ?)';
        $this->connection->executeQuery($expectedSQL, $ids)->willReturn($result);

        $expectedUsers = [$user1, $user2];
        $actualUsers = $this->queryCommand->execute(User::class, $ids);
        self::assertEquals($expectedUsers, $actualUsers);
    }

    public function testForOneId(): void
    {
        $ids = [1];

        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test1@test.de';

        $results = [
            ['id' => '1', 'name' => 'jon', 'email' => 'test1@test.de'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $expectedSQL = 'SELECT * FROM `user` WHERE `id` = ?';
        $this->connection->executeQuery($expectedSQL, $ids)->willReturn($result);

        $expectedUsers = [$user1];
        $actualUsers = $this->queryCommand->execute(User::class, $ids);
        self::assertEquals($expectedUsers, $actualUsers);
    }

    public function testForOneIdWithTableAttribute(): void
    {
        $ids = [1];

        $user1 = new User1();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test@test.de';

        $results = [
            ['id' => '1', 'name' => 'jon', 'email' => 'test@test.de'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $expectedSQL = 'SELECT * FROM `user` WHERE `id` = ?';
        $this->connection->executeQuery($expectedSQL, $ids)->willReturn($result);

        $expectedUsers = [$user1];
        $actualUsers = $this->queryCommand->execute(User1::class, $ids);
        self::assertEquals($expectedUsers, $actualUsers);
    }

    public function testForOneIdExecuteOne(): void
    {
        $id = 1;

        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jane';
        $user1->email = 'test1@test.de';

        $results = [
            ['id' => '1', 'name' => 'jane', 'email' => 'test1@test.de'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $expectedSQL = 'SELECT * FROM `user` WHERE `id` = ?';
        $this->connection->executeQuery($expectedSQL, [$id])->willReturn($result);

        $expectedUser = $user1;
        $actualUser = $this->queryCommand->executeForOne(User::class, $id);
        self::assertEquals($expectedUser, $actualUser);
    }

    public function testForOneIdExecuteOneButNothingFound(): void
    {
        $id = 1;
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator([]));

        $expectedSQL = 'SELECT * FROM `user` WHERE `id` = ?';
        $this->connection->executeQuery($expectedSQL, [$id])->willReturn($result);

        $actualUser = $this->queryCommand->executeForOne(User::class, $id);
        self::assertNull($actualUser);
    }

    public function testForEnum(): void
    {
        $ids = [1];

        $user = new User1();
        $user->id = 1;
        $user->name = 'jon';
        $user->email = 'test@test.de';
        $user->type = UserType::TYPE1();

        $results = [
            ['id' => '1', 'name' => 'jon', 'email' => 'test@test.de', 'type' => 'type1'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $expectedSQL = 'SELECT * FROM `user` WHERE `id` = ?';
        $this->connection->executeQuery($expectedSQL, $ids)->willReturn($result);

        $expectedUsers = [$user];
        $actualUsers = $this->queryCommand->execute(User1::class, $ids);
        self::assertEquals($expectedUsers, $actualUsers);
    }
}
