<?php

namespace App\Filters\Localization;

use App\Models\Localization\Translate;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class TranslateFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
    use LikeRawFilterTrait;

    public function query(string $query): void
    {
        $this
            ->likeRaw('place', $query)
            ->orWhere(
                fn(Builder $builder) => $this->likeRaw('key', $query, $builder)
            )
            ->orWhere(
                fn(Builder $builder) => $this->likeRaw('text', $query, $builder)
            );
    }

    public function key(string $key): void
    {
        $this->where('key', $key);
    }

    public function place(array $place): void
    {
        $this->whereIn('place', $place);
    }

    public function lang(array $lang): void
    {
        $this->whereIn('lang', $lang);
    }

    protected function allowedOrders(): array
    {
        return Translate::AVAILABLE_SORT_FIELDS;
    }
}
