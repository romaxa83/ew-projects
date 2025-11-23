<?php


namespace WezomCms\Core\Traits;


use Illuminate\Http\Request;

trait GetModelFilterWithProductsTrait
{
    protected function getFilters(Request $request): array
    {
        $filter = $request->all();

        if ($this->needsWithProductsFilter($request) && !$request->get('all', false)) {
            $filter['with_products'] = true;
        }

        unset($filter['all']);

        return $filter;
    }

    private function needsWithProductsFilter(Request $request): bool
    {
        return !$request->hasAny(['product_name', 'collection_id', 'category_id']);
    }
}
