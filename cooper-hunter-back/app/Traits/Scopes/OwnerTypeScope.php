<?php

namespace App\Traits\Scopes;


use App\Enums\Catalog\Products\ProductOwnerType;
use Illuminate\Database\Eloquent\Builder;

trait OwnerTypeScope
{
    public function scopeCooper(Builder|self $builder): void
    {
        $builder->where('owner_type', ProductOwnerType::COOPER());
    }

    public function scopeOlmo(Builder|self $builder): void
    {
        $builder->where('owner_type', ProductOwnerType::OLMO());
    }
}
