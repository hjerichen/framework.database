<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\FrameworkDatabase\DTO\Attributes\Table;
use HJerichen\FrameworkDatabase\DTO\DTO;

#[Table("user")]
class User1 implements DTO
{
    public int $id;
    public string $name;
    public string $email;
}
