<?php

namespace WezomCms\Cars\Http\Controllers\Admin;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Cars\Http\Requests\Admin\BrandRequest;
use WezomCms\Cars\Models\Brand;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\Http\Requests\Admin\DealershipsRequest;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Dealerships\Services\ScheduleServices;
use WezomCms\Regions\Repositories\CityRepository;

class BrandController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Brand::class;

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
    protected $view = 'cms-cars::admin.brands';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.car-brands';

    protected $hideCreateBnt = true;

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = BrandRequest::class;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $query
     * @param Request $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->orderBy('sort');
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-cars::admin.Brands');
    }
}


