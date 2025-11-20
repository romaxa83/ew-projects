<?php

namespace WezomCms\Cars\Http\Controllers\Admin;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Cars\Http\Requests\Admin\BrandRequest;
use WezomCms\Cars\Http\Requests\Admin\TransmissionRequest;
use WezomCms\Cars\Models\Brand;
use WezomCms\Cars\Models\Transmission;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\Http\Requests\Admin\DealershipsRequest;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Dealerships\Services\ScheduleServices;
use WezomCms\Regions\Repositories\CityRepository;

class TransmissionController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Transmission::class;

    /**
     * Indicates whether to use pagination.
     *
     * @var bool
     */
    protected $paginate = false;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-cars::admin.transmissions';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.car-transmissions';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = TransmissionRequest::class;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-cars::admin.Transmissions');
    }
}



