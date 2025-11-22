<?php

namespace App\Filters\Menu;

use App\Filters\BaseModelFilter;
use App\Models\Menu\Menu;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\LikeRawFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Menu
 */
class MenuFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use LikeRawFilterTrait;
    use SortFilterTrait;

    public function position(string $position): void
    {
        $this->where('position', $position);
    }

    public function block(string $block): void
    {
        $this->where('block', $block);
    }

    public function page(int $pageId): void
    {
        $this->where('page_id', $pageId);
    }

    public function query(string $query): void
    {
        $this->whereHas(
            'translations',
            fn(Builder $builder) => $this->likeRaw('title', $query, $builder)
        );
    }
}
