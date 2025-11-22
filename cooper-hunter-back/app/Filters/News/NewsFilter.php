<?php

declare(strict_types=1);

namespace App\Filters\News;

use App\Filters\BaseModelFilter;
use App\Models\News\News;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin News
 */
class NewsFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use SlugFilterTrait;
    use ActiveFilterTrait;
    use SortFilterTrait;

    public const TABLE = News::TABLE;

    public function query(string $query): void
    {
        $this->whereHas(
            'translation',
            function (Builder $builder) use ($query) {
                $builder->where(
                    function (Builder $builder) use ($query) {
                        $builder->orWhereRaw('LOWER(`title`) LIKE ?', ["%$query%"]);
                    }
                );
            }
        );
    }

    public function tag(int $tagId): void
    {
        $this->where('tag_id', $tagId);
    }
}
