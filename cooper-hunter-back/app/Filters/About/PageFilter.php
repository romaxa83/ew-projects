<?php

namespace App\Filters\About;

use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class PageFilter extends ModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;
    use ActiveFilterTrait;
    use SlugFilterTrait;

    public function query(string $query): void
    {
        $this->whereHas(
            'translations',
            fn(Builder $builder) => $this->likeRaw('title', $query, $builder)
        );
    }

    public function type(string $value): void
    {
        $this->where('slug', $value);
    }
}
