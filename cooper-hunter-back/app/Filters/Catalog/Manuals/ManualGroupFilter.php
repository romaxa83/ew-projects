<?php

namespace App\Filters\Catalog\Manuals;

use App\Models\Catalog\Manuals\ManualGroup;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin ManualGroup
 */
class ManualGroupFilter extends ModelFilter
{
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public function query(string $query)
    {
        $this->whereHas(
            'translations',
            fn(Builder $builder) => $this->likeRaw('lower(`title`)', $query, $builder)
        );
    }
}
