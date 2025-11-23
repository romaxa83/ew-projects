<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Catalog\Http\Requests\Admin\BrandRequest;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Traits\SettingControllerTrait;

class BrandsController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Brand::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-catalog::admin.brands';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.brands';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = BrandRequest::class;


    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-catalog::admin.brands.Brands');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $brands = Brand::search($request->get('term'));

        $results = [];
        if (!$request->get('page')) {
            $results[] = ['id' => '', 'text' => __('cms-core::admin.layout.Not set')];
        }
        foreach ($brands as $brand) {
            $results[] = [
                'id' => $brand->id,
                'text' => $brand->name,
            ];
        }

        return $this->success([
            'results' => $results,
            'pagination' => [
                'more' => $brands->hasMorePages(),
            ],
        ]);
    }

    /**
     * @param $id
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function setSort($id, Request $request)
    {
        $brand = Brand::findOrFail($id);

        $this->authorizeForAction('edit', $brand);

        $brand->sort = $request->get('sort', 0);

        $brand->save();

        return $this->success(['message' => __('cms-core::admin.layout.Data successfully updated')]);
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->sorting();
    }


    /**
     * @return array
     * @throws \Exception
     */
    protected function settings(): array
    {
        return [AdminLimit::make()];
    }
}
