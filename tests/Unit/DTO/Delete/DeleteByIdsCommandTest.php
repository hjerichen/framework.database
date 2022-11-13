<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Delete;

use Doctrine\DBAL\Connection;
use HJerichen\FrameworkDatabase\DTO\Delete\DeleteByIdsCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DeleteByIdsCommandTest extends TestCase
{
    use  ProphecyTrait;

    private DeleteByIdsCommand $command;
    private ObjectProphecy|Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);

        $this->command = new DeleteByIdsCommand($this->connection->reveal());
    }

    /* TESTS */

    public function testWithNoIds(): void
    {
        $this->connection->executeStatement(Argument::any())->shouldNotBeCalled();

        $expected = 0;
        $actual = $this->command->execute(User::class, []);
        self::assertEquals($expected, $actual);
    }

    public function testForOneId(): void
    {
        $ids = [2];

        $expectedSQL = 'DELETE FROM `user` WHERE `id` = ?';
        $this->connection->executeStatement($expectedSQL, $ids)->willReturn(1);

        $expected = 1;
        $actual = $this->command->execute(User::class, $ids);
        self::assertEquals($expected, $actual);
    }

    public function testForMultipleIds(): void
    {
        $ids = [1, 4, 7];

        $expectedSQL = 'DELETE FROM `user` WHERE `id` in (?, ?, ?)';
        $this->connection->executeStatement($expectedSQL, $ids)->willReturn(2);

        $expectedUsers = 2;
        $actualUsers = $this->command->execute(User::class, $ids);
        self::assertEquals($expectedUsers, $actualUsers);
    }
}
