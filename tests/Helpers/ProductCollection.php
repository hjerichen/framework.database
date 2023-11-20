<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\Test\Helpers;

use HJerichen\Collections\ObjectWithIdCollection;

/** @template-extends ObjectWithIdCollection<Product> */
class ProductCollection extends ObjectWithIdCollection
{
    /** @param Product[] $items */
    public function __construct(
        array $items = []
    ) {
        parent::__construct(Product::class, $items);
    }
}
