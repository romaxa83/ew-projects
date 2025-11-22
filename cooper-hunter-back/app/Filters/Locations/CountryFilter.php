<?php

namespace App\Filters\Locations;

use App\Models\Locations\Country;
use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Country
 */
class CountryFilter extends ModelFilter
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

    public function active(bool $value): void
    {
        $this->where('active', $value);
    }

    public function default($value): void
    {
        $this->where('default', $value);
    }
}

