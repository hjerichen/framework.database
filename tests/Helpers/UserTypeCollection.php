<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\Collections\Collection;
use HJerichen\Collections\ObjectCollection;

/**
 * @extends ObjectCollection<UserType>
 * @extends Collection<UserType>
 */
class UserTypeCollection extends ObjectCollection
{
    /** @param UserType[] $items */
    public function __construct(
        array $items = []
    ) {
        parent::__construct(UserType::class, $items);
    }
}
