<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\FrameworkDatabase\DTO\DTO;

class Product implements DTO
{
    public int $id;
    public string $ean;
}
