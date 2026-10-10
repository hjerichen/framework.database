<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\FrameworkDatabase\DTO\DTOCollection;

/** @extends DTOCollection<User> */
class UserDTOCollection extends DTOCollection
{
    public function __construct()
    {
        parent::__construct(User::class);
    }
}
