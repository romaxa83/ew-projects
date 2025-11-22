<?php

namespace App\Collections\Catalog\Categories;

use App\Models\Catalog\Categories\Category;
use ArrayIterator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Category|null first(callable $callback = null, $default = null)
 * @method Category|null last(callable $callback = null, $default = null)
 * @method Category|null get($key, $default = null)
 * @method Category|null pop()
 * @method Category|null shift()
 * @method ArrayIterator|Category[] getIterator()
 *
 * @property Category[] items
 */
class CategoryCollection extends Collection
{
}
