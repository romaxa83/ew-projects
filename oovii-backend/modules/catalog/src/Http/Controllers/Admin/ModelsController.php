<?php

namespace WezomCms\Catalog\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Catalog\Http\Requests\Admin\ModelRequest;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Catalog\Models\Model;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Traits\SettingControllerTrait;

class ModelsController extends AbstractCRUDController
{
    use SettingControllerTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-catalog::admin.models';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.models';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = ModelRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-catalog::admin.models.Models');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $models = Model::search($request->get('term'), $request->only('brand_id'));

        $results = [];
        if (!$request->get('page')) {
            $results[] = ['id' => '', 'text' => __('cms-core::admin.layout.Not set')];
        }
        foreach ($models as $model) {
            $results[] = [
                'id' => $model->id,
                'text' => $model->name,
            ];
        }

        return $this->success([
            'results' => $results,
            'pagination' => [
                'more' => $models->hasMorePages(),
            ],
        ]);
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->with('brand');
    }

    /**
     * @param  Model  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function createViewData($obj, array $viewData): array
    {
        return [
            'selectedBrand' => old('brand_id') ? Brand::find(old('brand_id')) : null,
        ];
    }

    /**
     * @param  Model  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function editViewData($obj, array $viewData): array
    {
        return [
            'selectedBrand' => $obj->brand,
        ];
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
