<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    public function __construct(
        public string $name
    ) {
    }
}
