<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use DateTime;
use DateTimeImmutable;
use HJerichen\FrameworkDatabase\DTO\Attributes\Table;
use HJerichen\FrameworkDatabase\DTO\DTO;

#[Table("user")]
class User1 implements DTO
{
    public int $id;
    public string $name;
    public string $email;
    public ?array $categories;
    public ?UserType $type;
    public ?DateTime $date;
    public ?DateTimeImmutable $dateImmutable;
    public ?UserTypeCollection $types;
}
