<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Factory;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @mixin Factory
 */
trait SortableFactoryTrait
{
    public function sort(int $sort, string $column = 'sort'): self
    {
        return $this->set($column, $sort);
    }
}
