<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Database\Schema;

use Doctrine\DBAL\Schema\Schema;

interface Table
{
    public function addToSchema(Schema $schema): void;
}