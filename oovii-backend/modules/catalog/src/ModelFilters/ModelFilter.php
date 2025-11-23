<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter as EloquentModelFilter;
use Illuminate\Http\Request;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Model;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;

/**
 * Class ModelFilter
 * @package WezomCms\Catalog\ModelFilters
 * @mixin Model
 */
class ModelFilter extends EloquentModelFilter implements FilterListFieldsInterface
{
    protected $brands = [];

    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $brandsSelect = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options($this->brands)
            ->name('brand_id')
            ->label(__('cms-catalog::admin.models.Brand'))
            ->class('js-ajax-select2')
            ->attributes(['data-url' => route('admin.brands.search')]);

        return [
            $brandsSelect,
            FilterField::makeName(),
            FilterField::published(),
        ];
    }

    /**
     * @param  Request  $request
     */
    public function restoreSelectedOptions(Request $request)
    {
        if ($brandId = $request->get('brand_id')) {
            $brand = Brand::find($brandId);
            if ($brand) {
                $this->brands = [$brand->id => $brand->name];
            }
        }
    }

    public function brand($id)
    {
        $this->where('brand_id', $id);
    }

    public function name($name)
    {
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function published($published)
    {
        $this->where('published', $published);
    }
}
