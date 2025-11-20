<?php

namespace WezomCms\Regions\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Regions\Http\Requests\Admin\CityRequest;
use WezomCms\Regions\Http\Requests\Admin\RegionRequest;
use WezomCms\Regions\Models\City;
use WezomCms\Regions\Models\Region;
use WezomCms\Regions\Repositories\RegionsRepository;

class CityController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = City::class;

    /**
     * Indicates whether to use pagination.
     *
     * @var bool
     */
    protected $paginate = true;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-regions::admin.city';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.cities';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = CityRequest::class;

    /**
     * @var RegionsRepository
     */
    private $regionsRepository;

    public function __construct(RegionsRepository $regionsRepository)
    {
        parent::__construct();

        $this->regionsRepository = $regionsRepository;
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-regions::admin.Cities');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->orderBy('sort');
    }

    /**
     * @param  City $model
     * @param  array  $viewData
     * @return array
     */
    protected function createViewData($model, array $viewData): array
    {
        return [
            'regions' => $this->regionsRepository->forSelect(),
            'selectedRegion' => [],
        ];
    }

    /**
     * @param  City $model
     * @param  array  $viewData
     * @return array
     */
    protected function editViewData($model, array $viewData): array
    {
        return [
            'regions' => $this->regionsRepository->forSelect(),
            'selectedRegion' => [$model->region_id],
        ];
    }

}


