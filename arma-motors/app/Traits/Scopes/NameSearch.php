<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

trait NameSearch
{
    public function scopeNameSearch(EloquentBuilder $query, string $search): EloquentBuilder
    {
        return $query->where('name','like', '%' . $search . '%');
    }
}
