<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Unit\DTO;

use DateTime;
use DateTimeImmutable;
use HJerichen\Collections\ObjectCollection;
use HJerichen\FrameworkDatabase\DTO\DTO;
use HJerichen\FrameworkDatabase\DTO\Utils;
use HJerichen\FrameworkDatabase\Test\Helpers\Product;
use HJerichen\FrameworkDatabase\Test\Helpers\ProductCollection;
use HJerichen\FrameworkDatabase\Test\Helpers\User;
use HJerichen\FrameworkDatabase\Test\Helpers\User1;
use HJerichen\FrameworkDatabase\Test\Helpers\User2;
use HJerichen\FrameworkDatabase\Test\Helpers\UserType;
use HJerichen\FrameworkDatabase\Test\Helpers\UserTypeCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UtilsTest extends TestCase
{
    public function testConvertObjectToArrayForNothingSet(): void
    {
        $user = new User();

        $expected = [];
        $actual = Utils::convertObjectToArray($user);
        self::assertEquals($expected, $actual);
    }

    public function testConvertObjectToArrayForAllSet(): void
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'jon';
        $user->email = 'test@test.de';
        $user->value = 15.88;
        $user->active = true;

        $expected = [
            'id' => 1,
            'name' => 'jon',
            'email' => 'test@test.de',
            'value' => 15.88,
            'active' => true,
        ];
        $actual = Utils::convertObjectToArray($user);
        self::assertEquals($expected, $actual);
    }

    public function testConvertObjectToArrayForNotAllSet(): void
    {
        $user = new User();
        $user->id = 1;
        $user->name = 'jon';

        $expected = [
            'id' => 1,
            'name' => 'jon',
        ];
        $actual = Utils::convertObjectToArray($user);
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForDataIsEmpty(): void
    {
        $data = [];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = new User();
        $actual = $user;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForData(): void
    {
        $data = [
            'id' => 4,
            'name' => 'jon'
        ];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = new User();
        $expected->id = 4;
        $expected->name = 'jon';
        $actual = $user;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForStringNull(): void
    {
        $data = [
            'id' => 4,
            'name' => null
        ];
        $user = new User2();

        Utils::populateObject($user, $data);

        $expected = null;
        $actual = $user->name;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForDataIsNotProperty(): void
    {
        $data = [
            'id' => 4,
            'name' => 'jon',
            'something' => 'test'
        ];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = new User();
        $expected->id = 4;
        $expected->name = 'jon';
        $actual = $user;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForStringToInt(): void
    {
        $data = ['id' => '4'];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = 4;
        $actual = $user->id;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForStringToIntNull(): void
    {
        $data = ['logins' => null];
        $user = new User2();

        Utils::populateObject($user, $data);

        $expected = null;
        $actual = $user->logins;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForStringToFloat(): void
    {
        $data = ['value' => '4.88'];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = 4.88;
        $actual = $user->value;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForStringToFloatNull(): void
    {
        $data = ['value' => null];
        $user = new User2();

        Utils::populateObject($user, $data);

        $expected = null;
        $actual = $user->value;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForIntToFloat(): void
    {
        $data = ['value' => 4];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = 4.0;
        $actual = $user->value;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForIntToString(): void
    {
        $data = ['name' => 444];
        $user = new User();

        Utils::populateObject($user, $data);

        $expected = '444';
        $actual = $user->name;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForStringToBoolTrue(): void
    {
        $data = ['active' => '1'];
        $user = new User();

        Utils::populateObject($user, $data);

        $actual = $user->active;
        self::assertTrue($actual);
    }

    public function testPopulateObjectForStringToBoolFalse(): void
    {
        $data = ['active' => '0'];
        $user = new User();

        Utils::populateObject($user, $data);

        $actual = $user->active;
        self::assertFalse($actual);
    }

    public function testPopulateObjectForStringToBoolNull(): void
    {
        $data = ['active' => null];
        $user = new User2();

        Utils::populateObject($user, $data);

        $expected = null;
        $actual = $user->active;
        self::assertSame($expected, $actual);
    }

    public function testPopulateObjectForStringToEnum(): void
    {
        $data = ['type' => 'type1'];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = UserType::TYPE1();
        $actual = $user->type;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForStringToEnumNull(): void
    {
        $data = ['type' => null];
        $user = new User1();

        Utils::populateObject($user, $data);

        $actual = $user->type;
        self::assertNull($actual);
    }

    public function testPopulateObjectForStringToDate(): void
    {
        $data = ['date' => '2020-10-01 08:00:00'];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = '01.10.2020 08:00:00';
        $actual = $user->date?->format('d.m.Y H:i:s');
        self::assertInstanceOf(DateTime::class, $user->date);
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForStringToDateForNull(): void
    {
        $data = ['date' => null];
        $user = new User1();

        Utils::populateObject($user, $data);

        self::assertNull($user->date);
    }

    public function testPopulateObjectForStringToDateImmutable(): void
    {
        $data = ['dateImmutable' => '2020-10-01 08:00:00'];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = '01.10.2020 08:00:00';
        $actual = $user->dateImmutable?->format('d.m.Y H:i:s');
        self::assertInstanceOf(DateTimeImmutable::class, $user->dateImmutable);
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForStringToDateImmutableForNull(): void
    {
        $data = ['dateImmutable' => null];
        $user = new User1();

        Utils::populateObject($user, $data);

        self::assertNull($user->dateImmutable);
    }

    public function testPopulateObjectForJsonStringToArray(): void
    {
        $data = ['categories' => json_encode(['cat1', 'cat2'], JSON_THROW_ON_ERROR)];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = ['cat1', 'cat2'];
        $actual = $user->categories;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForArrayToArray(): void
    {
        $data = ['categories' => ['cat1', 'cat2']];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = ['cat1', 'cat2'];
        $actual = $user->categories;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForJsonStringToArrayNull(): void
    {
        $data = ['categories' => null];
        $user = new User1();

        Utils::populateObject($user, $data);

        $actual = $user->categories;
        self::assertNull($actual);
    }

    public function testPopulateObjectForJsonStringToEnumCollection(): void
    {
        $data = ['types' => '["type1","type2"]'];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = new UserTypeCollection([
            UserType::TYPE1(),
            UserType::TYPE2(),
        ]);
        $actual = $user->types;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForJsonStringToEnumCollectionNull(): void
    {
        $data = ['types' => null];
        $user = new User1();

        Utils::populateObject($user, $data);

        $actual = $user->types;
        self::assertNull($actual);
    }

    public function testPopulateObjectForArrayToEnumCollection(): void
    {
        $data = ['types' => ["type1", "type2"]];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = new UserTypeCollection([
            UserType::TYPE1(),
            UserType::TYPE2(),
        ]);
        $actual = $user->types;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForJsonStringToDTO(): void
    {
        $data = ['product' => '{"id":10,"ean":"test"}'];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = new Product();
        $expected->id = 10;
        $expected->ean = 'test';
        $actual = $user->product;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForArrayToDTO(): void
    {
        $data = ['product' => ['id' => 10, 'ean' => 'test']];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected = new Product();
        $expected->id = 10;
        $expected->ean = 'test';
        $actual = $user->product;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForJSONStringToDTOCollection(): void
    {
        $data = ['products' => '[{"id":10,"ean":"test1"},{"id":11,"ean":"test2"}]'];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected1 = new Product();
        $expected1->id = 10;
        $expected1->ean = 'test1';

        $expected2 = new Product();
        $expected2->id = 11;
        $expected2->ean = 'test2';

        $expected = new ProductCollection([$expected1, $expected2]);
        $actual = $user->products;
        self::assertEquals($expected, $actual);
    }

    public function testPopulateObjectForArrayToDTOCollection(): void
    {
        $data = [
            'products' => [
                ['id' => 10, 'ean' => 'test1'],
                ['id' => 11, 'ean' => 'test2'],
            ]
        ];
        $user = new User1();

        Utils::populateObject($user, $data);

        $expected1 = new Product();
        $expected1->id = 10;
        $expected1->ean = 'test1';

        $expected2 = new Product();
        $expected2->id = 11;
        $expected2->ean = 'test2';

        $expected = new ProductCollection([$expected1, $expected2]);
        $actual = $user->products;
        self::assertEquals($expected, $actual);
    }

    public function test_buildDTOCollection(): void
    {
        $actual = Utils::buildDTOCollection(ProductCollection::class, [
            ['id' => 10, 'ean' => '123'],
            ['id' => 11, 'ean' => '1234', 'alternative' => ['id' => 444]],
        ]);

        $product1 = new Product();
        $product1->id = 10;
        $product1->ean = '123';
        $product2 = new Product();
        $product2->id = 11;
        $product2->ean = '1234';
        $product2->alternative = new Product();
        $product2->alternative->id = 444;
        $expected = new ProductCollection([$product1, $product2]);

        $this->assertEquals($expected, $actual);
    }

    public function test_buildDTOCollection_withNoClassStringOfCollection(): void
    {
        $expected = new RuntimeException('No collection provided.');
        $this->expectExceptionObject($expected);

        Utils::buildDTOCollection(Product::class, []);
    }

    public function test_buildDTO_withNoDTO(): void
    {
        $expected = new RuntimeException('Class HJerichen\FrameworkDatabase\Test\Unit\DTO\UtilsTest does not implement DTO.');
        $this->expectExceptionObject($expected);

        Utils::buildDTO(self::class, []);
    }

    public function test_convertToHashes(): void
    {
        $product1 = new Product();
        $product1->id = 10;
        $product1->ean = '123';
        $product2 = new Product();
        $product2->id = 11;
        $product2->ean = '1234';
        $product2->alternative = new Product();
        $product2->alternative->id = 444;
        $products = new ProductCollection([$product1, $product2]);

        $expected = [
            ['id' => 10, 'ean' => '123'],
            ['id' => 11, 'ean' => '1234', 'alternative' => ['id' => 444]],
        ];
        /** @var ObjectCollection<DTO> $products */
        $actual = Utils::convertToHashes($products);
        $this->assertEquals($expected, $actual);
    }
}
