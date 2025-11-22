<?php

namespace App\Traits\Models;

use Illuminate\Database\Eloquent\Builder;

trait IsJoinedTrait
{
    public function isJoined(Builder $builder, string $table): bool
    {
        foreach($builder->getQuery()->joins ?? [] as $join) {
            if ($join->table == $table) {
                return true;
            }
        }

        return false;
    }
}
