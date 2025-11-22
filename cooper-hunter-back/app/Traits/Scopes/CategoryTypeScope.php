<?php

namespace App\Traits\Scopes;

use App\Enums\Categories\CategoryTypeEnum;
use Illuminate\Database\Eloquent\Builder;

trait CategoryTypeScope
{
    public function scopeCommercial(Builder|self $builder): void
    {
        $builder->where('type', CategoryTypeEnum::COMMERCIAL());
    }
}
