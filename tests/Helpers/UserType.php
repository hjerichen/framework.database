<?php /** @noinspection PhpUnusedPrivateFieldInspection */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\Framework\Types\Enum;

/**
 * @method static TYPE1()
 * @method static TYPE2()
 */
class UserType extends Enum
{
    private const TYPE1 = 'type1';
    private const TYPE2 = 'type2';
}
