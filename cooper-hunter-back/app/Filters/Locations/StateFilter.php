<?php

namespace App\Filters\Locations;

use App\Models\Locations\State;
use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin State
 */
class StateFilter extends ModelFilter
{
    use IdFilterTrait;

    public function name(string $name): void
    {
        $name = strtolower($name);

        $this->whereHas(
            'translation',
            function (Builder $builder) use ($name) {
                $builder->where(
                    function (Builder $builder) use ($name) {
                        $builder->orWhereRaw('LOWER(`name`) LIKE ?', ["%$name%"]);
                    }
                );
            }
        );
    }

    public function status(bool $status): void
    {
        $this->where('status', $status);
    }

    public function country($value): void
    {
        $this->where('country_id', $value);
    }

    public function countryCode($value): void
    {
        $this->whereHas('country', function(Builder $b) use ($value){
            return $b->where('country_code', $value);
        });
    }
}
