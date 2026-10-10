<?php
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

use HJerichen\Collections\ObjectWithIdCollection;

/**
 * @template T of DTO
 * @template-extends ObjectWithIdCollection<T>
 */
class DTOCollection extends ObjectWithIdCollection
{
}