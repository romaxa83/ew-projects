<?php

declare(strict_types=1);

namespace Wezom\Core\Traits\Model;

use Illuminate\Database\Eloquent\Builder;

trait SortModelTrait
{
    public function scopeOrderBySort(Builder|self $query): void
    {
        $query->orderBy('sort')->orderByDesc('id');
    }
}
