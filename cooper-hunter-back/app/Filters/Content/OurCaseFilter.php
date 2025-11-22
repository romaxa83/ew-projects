<?php

namespace App\Filters\Content;

use App\Filters\BaseModelFilter;
use App\Models\Content\OurCases\OurCase;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin OurCase
 */
class OurCaseFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;
    use SlugFilterTrait;

    public function ourCaseCategory(int $category): void
    {
        $this->where('our_case_category_id', $category);
    }

    public function ourCaseCategorySlug(string $slug): void
    {
        $this->whereHas('category', function (Builder $builder) use ($slug) {
            $builder->where('slug', $slug);
        });
    }
}
