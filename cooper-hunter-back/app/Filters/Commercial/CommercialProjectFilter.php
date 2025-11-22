<?php

namespace App\Filters\Commercial;

use App\Filters\BaseModelFilter;
use App\Models\Commercial\CommercialProject;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin CommercialProject
 */
class CommercialProjectFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public function status(string $status): void
    {
        $this->where('status', $status);
    }

    public function query(string $query): void
    {
        $query = $this->toLower($query);

        $this->where(
            fn(Builder $builder) => $builder
                ->whereRaw($this->wrapLikeRaw('code', $query))
                ->orWhereRaw($this->wrapLikeRaw('address_line_1', $query))
                ->orWhereRaw($this->wrapLikeRaw('city', $query))
//                ->orWhereRaw($this->wrapLikeRaw('state', $query))
                ->orWhereRaw($this->wrapLikeRaw('zip', $query))
        );
    }

    public function name(string $value): void
    {
        $query = $this->toLower($value);

        $this->where(fn(Builder $builder) => $builder->whereRaw($this->wrapLikeRaw('name', $query)));
    }
}
