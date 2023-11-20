<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO\Save;

use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use HJerichen\FrameworkDatabase\DTO\Save\SaveCommand;
use HJerichen\FrameworkDatabase\Test\Helpers\Product;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\User1;
use HJerichen\FrameworkDatabase\Test\Helpers\User2;
use HJerichen\FrameworkDatabase\Test\Helpers\UserType;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SaveCommandTest extends TestCase
{
    use ProphecyTrait;

    private SaveCommand $command;
    /** @var ObjectProphecy<Connection> */
    private ObjectProphecy $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->prophesize(Connection::class);
        $this->connection->lastInsertId()->willReturn(8);

        $this->command = new SaveCommand($this->connection->reveal());
    }

    /* TESTS */

    public function testWithOneObject(): void
    {
        $user = new User();
        $user->name = 'jon';
        $user->email = 'test';

        $expectedSQL = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters = [
            'name_1' => 'jon',
            'email_1' => 'test'
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user]);
    }

    public function testWithOneObjectHasAttribute(): void
    {
        $user = new User1();
        $user->name = 'jon';
        $user->email = 'test';

        $expectedSQL = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `categories` = `categories`, `type` = `type`, `date` = `date`, `dateImmutable` = `dateImmutable`, `types` = `types`, `product` = `product`, `products` = `products`
            SQL;
        $expectedParameters = [
            'name_1' => 'jon',
            'email_1' => 'test'
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user]);
    }

    public function testWithMultipleObjects(): void
    {
        $user1 = new User();
        $user1->name = 'jon';
        $user1->email = 'test';

        $user2 = new User();
        $user2->name = 'doe';
        $user2->email = 'test2';

        $expectedSQL = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1), (:name_2, :email_2)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters = [
            'name_1' => 'jon',
            'email_1' => 'test',
            'name_2' => 'doe',
            'email_2' => 'test2',
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user1, $user2]);
    }

    public function testWithEmptyArray(): void
    {
        $this->connection
            ->executeStatement(Argument::any(), Argument::any())
            ->shouldNotBeCalled();
        $this->command->execute([]);
    }

    public function testWithDifferentParametersSet(): void
    {
        $user1 = new User();
        $user1->id = 33;
        $user1->name = 'jon';

        $user2 = new User();
        $user2->name = 'doe';
        $user2->email = 'test2';

        $expectedSQL1 = <<<SQL
            INSERT INTO `user`
                (`id`, `name`)
            VALUES
                (:id_1, :name_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `active` = `active`, `value` = `value`, `email` = `email`
            SQL;
        $expectedParameters1 = [
            'id_1' => 33,
            'name_1' => 'jon',
        ];
        $this->connection
            ->executeStatement($expectedSQL1, $expectedParameters1)
            ->shouldBeCalledOnce();

        $expectedSQL2 = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters2 = [
            'name_1' => 'doe',
            'email_1' => 'test2',
        ];
        $this->connection
            ->executeStatement($expectedSQL2, $expectedParameters2)
            ->shouldBeCalledOnce();

        $this->command->execute([$user1, $user2]);
    }

    public function testWithObjectsForDifferentTables(): void
    {
        $user1 = new Product();
        $user1->id = 33;
        $user1->ean = '1234';

        $user2 = new User();
        $user2->name = 'doe';
        $user2->email = 'test2';

        $expectedSQL1 = <<<SQL
            INSERT INTO `product`
                (`id`, `ean`)
            VALUES
                (:id_1, :ean_1)
            ON DUPLICATE KEY UPDATE
                `ean` = VALUES(`ean`)
            SQL;
        $expectedParameters1 = [
            'id_1' => 33,
            'ean_1' => '1234',
        ];
        $this->connection
            ->executeStatement($expectedSQL1, $expectedParameters1)
            ->shouldBeCalledOnce();

        $expectedSQL2 = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters2 = [
            'name_1' => 'doe',
            'email_1' => 'test2',
        ];
        $this->connection
            ->executeStatement($expectedSQL2, $expectedParameters2)
            ->shouldBeCalledOnce();

        $this->command->execute([$user1, $user2]);
    }

    public function testDTOGetIds(): void
    {
        $user1 = new User();
        $user1->name = 'jon';
        $user1->email = 'test1';

        $user2 = new User();
        $user2->name = 'doe';
        $user2->email = 'test2';

        $user3 = new User();
        $user3->name = 'jane';
        $user3->email = 'test3';

        $expectedSQL = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1), (:name_2, :email_2), (:name_3, :email_3)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters = [
            'name_1' => 'jon',
            'email_1' => 'test1',
            'name_2' => 'doe',
            'email_2' => 'test2',
            'name_3' => 'jane',
            'email_3' => 'test3',
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user1, $user2, $user3]);

        self::assertSame(8, $user1->id);
        self::assertSame(9, $user2->id);
        self::assertSame(10, $user3->id);
    }

    /** @psalm-suppress RedundantCondition */
    public function testDTOGetIdsMixed(): void
    {
        $user1 = new User();
        $user1->id = 1;
        $user1->name = 'jon';
        $user1->email = 'test1';

        $user2 = new User();
        $user2->name = 'doe';
        $user2->email = 'test2';

        $user3 = new User();
        $user3->id = 2;
        $user3->name = 'jane';
        $user3->email = 'test3';

        $expectedSQL1 = <<<SQL
            INSERT INTO `user`
                (`id`, `name`, `email`)
            VALUES
                (:id_1, :name_1, :email_1), (:id_2, :name_2, :email_2)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters1 = [
            'id_1' => 1,
            'name_1' => 'jon',
            'email_1' => 'test1',
            'id_2' => 2,
            'name_2' => 'jane',
            'email_2' => 'test3',
        ];
        $this->connection
            ->executeStatement($expectedSQL1, $expectedParameters1)
            ->shouldBeCalledOnce();

        $expectedSQL2 = <<<SQL
            INSERT INTO `user`
                (`name`, `email`)
            VALUES
                (:name_1, :email_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `email` = VALUES(`email`), `active` = `active`, `value` = `value`
            SQL;
        $expectedParameters2 = [
            'name_1' => 'doe',
            'email_1' => 'test2',
        ];
        $this->connection
            ->executeStatement($expectedSQL2, $expectedParameters2)
            ->shouldBeCalledOnce();

        $this->command->execute([$user1, $user2, $user3]);

        self::assertSame(1, $user1->id);
        self::assertSame(8, $user2->id);
        self::assertSame(2, $user3->id);
    }

    public function testWithEnum(): void
    {
        $user = new User1();
        $user->name = 'jon';
        $user->type = UserType::TYPE1();

        $expectedSQL = <<<SQL
            INSERT INTO `user`
                (`name`, `type`)
            VALUES
                (:name_1, :type_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `type` = VALUES(`type`), `email` = `email`, `categories` = `categories`, `date` = `date`, `dateImmutable` = `dateImmutable`, `types` = `types`, `product` = `product`, `products` = `products`
            SQL;
        $expectedParameters = [
            'name_1' => 'jon',
            'type_1' => 'type1'
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user]);
    }

    public function testWithDateTime(): void
    {
        $user = new User1();
        $user->name = 'jon';
        $user->date = new DateTime('2020-10-01 09:00:00');
        $user->dateImmutable = new DateTimeImmutable('2020-10-01 10:00:00');

        $expectedSQL = <<<SQL
            INSERT INTO `user`
                (`name`, `date`, `dateImmutable`)
            VALUES
                (:name_1, :date_1, :dateImmutable_1)
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`), `date` = VALUES(`date`), `dateImmutable` = VALUES(`dateImmutable`), `email` = `email`, `categories` = `categories`, `type` = `type`, `types` = `types`, `product` = `product`, `products` = `products`
            SQL;
        $expectedParameters = [
            'name_1' => 'jon',
            'date_1' => '2020-10-01 09:00:00',
            'dateImmutable_1' => '2020-10-01 10:00:00',
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user]);
    }

    public function testDTOOnlyHasID(): void
    {
        $user1 = new User();
        $user1->id = 1;

        $this->connection
            ->executeStatement(Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->command->execute([$user1]);
    }

    public function testWithBoolean(): void
    {
        $user1 = new User2();
        $user1->name = 'jon1';
        $user1->active = true;

        $user2 = new User2();
        $user2->name = 'jon2';
        $user2->active = false;

        $expectedSQL = <<<SQL
            INSERT INTO `user2`
                (`active`, `name`)
            VALUES
                (:active_1, :name_1), (:active_2, :name_2)
            ON DUPLICATE KEY UPDATE
                `active` = VALUES(`active`), `name` = VALUES(`name`), `logins` = `logins`, `value` = `value`
            SQL;
        $expectedParameters = [
            'active_1' => 1,
            'name_1' => 'jon1',
            'active_2' => 0,
            'name_2' => 'jon2',
        ];
        $this->connection
            ->executeStatement($expectedSQL, $expectedParameters)
            ->shouldBeCalledOnce();
        $this->command->execute([$user1, $user2]);
    }
}
