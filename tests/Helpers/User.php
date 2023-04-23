<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\FrameworkDatabase\DTO\DTO;

/**
 * @psalm-suppress PossiblyUnusedProperty
 */
class User implements DTO
{
    public int $id;
    public bool $active;
    public float $value;
    public string $name;
    public string $email;
}
