<?php
/** @noinspection PhpRedundantOptionalArgumentInspection */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Query;

use ArrayIterator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithFieldFilterCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class QueryWithFieldFilterCommandTest extends TestCase
{
    use ProphecyTrait;

    private QueryWithFieldFilterCommand $queryCommand;
    private ObjectProphecy|Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);

        $this->queryCommand = new QueryWithFieldFilterCommand($this->connection->reveal());
    }

    public function testForEmptyFieldArray(): void
    {
        $expectedSQL = "SELECT * FROM `user`";

        $results = [
            ['id' => '1'],
            ['id' => '7'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $this->connection->executeQuery($expectedSQL, [])->willReturn($result);

        $expectedUser1 = new User();
        $expectedUser1->id = 1;
        $expectedUser2 = new User();
        $expectedUser2->id = 7;

        $expected = [$expectedUser1, $expectedUser2];
        $actual = $this->queryCommand->execute(User::class);
        self::assertEquals($expected, $actual);
    }

    public function testForFieldArray(): void
    {
        $fields = [
            'name' => 'test',
            'email' => 'test@test.de'
        ];
        $expectedSQL = "SELECT * FROM `user` WHERE `name` = :name AND `email` = :email";

        $results = [
            ['id' => '1'],
            ['id' => '7'],
        ];
        $result = $this->prophesize(Result::class);
        $result->iterateAssociative()->willReturn(new ArrayIterator($results));

        $this->connection->executeQuery($expectedSQL, $fields)->willReturn($result);

        $expectedUser1 = new User();
        $expectedUser1->id = 1;
        $expectedUser2 = new User();
        $expectedUser2->id = 7;

        $expected = [$expectedUser1, $expectedUser2];
        $actual = $this->queryCommand->execute(User::class, $fields);
        self::assertEquals($expected, $actual);
    }
}
