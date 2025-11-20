<?php

namespace App\ModelFilters\User;

use App\Helpers\ParseQueryParams;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends ModelFilter
{
    public function role($value): self
    {
        return $this->whereHas('roles', function(Builder $query) use ($value){
            $query->where('role', $value);
        });
    }

    public function login($value): self
    {
        return $this->where('login', 'like', $value . '%');
    }

    public function email($value): self
    {
        return $this->where('email', $value);
    }

    public function country($value): self
    {
        return $this->where('nationality_id', $value);
    }

    public function dealer($value): self
    {
        return $this->where(function(Builder $query) use($value) {
            $query->whereHas('dealer', function(Builder $q) use ($value){
                $q->where('name', 'like', $value.'%');
            })
                ->orWhereHas('dealers', function (Builder $q) use ($value){
                    $q->where('name', 'like', $value.'%');
                });
        });
    }

    public function name($value): self
    {
        $name = ParseQueryParams::name($value);
        return $this->whereHas('profile', function(Builder $q) use($name) {
            if(count($name) == 1){
                $q->whereRaw("(first_name LIKE '{$name[0]}%' OR last_name LIKE '{$name[0]}%')");
            } else {
                $q->whereRaw("(first_name LIKE '{$name[0]}%' AND last_name LIKE '{$name[1]}%')");
            }
        });
    }
}

