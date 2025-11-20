<?php

namespace WezomCms\Dealerships\Http\Controllers\Admin;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Cars\Repositories\CarBrandRepository;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\Http\Requests\Admin\DealershipsRequest;
use WezomCms\Dealerships\Models\Dealership;
use WezomCms\Dealerships\Services\ScheduleServices;
use WezomCms\Regions\Repositories\CityRepository;

class DealershipsController extends AbstractCRUDController
{
    use CoordsTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Dealership::class;

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
    protected $view = 'cms-dealerships::admin.dealerships';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.dealerships';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = DealershipsRequest::class;

    private CityRepository $cityRepository;
    private ScheduleServices $scheduleServices;
    private CarBrandRepository $carBrandRepository;

    public function __construct(
        CityRepository $cityRepository,
        ScheduleServices $scheduleServices,
        CarBrandRepository $carBrandRepository
    )
    {
        parent::__construct();

        $this->cityRepository = $cityRepository;
        $this->scheduleServices = $scheduleServices;
        $this->carBrandRepository = $carBrandRepository;
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-dealerships::admin.Dealerships');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->countsRate()->orderBy('sort');
    }

    /**
     * @todo добавить к телефону описание
     * @param  Dealership $model
     * @param  FormRequest  $request
     * @return array
     */
    protected function fill($model, FormRequest $request): array
    {
        $dataPhones = $request->get('phones', []);

        $phones = [];
        for ($i = 0; $i < count($dataPhones); $i=($i+3)){
            $phones[$i]['phone'] = $dataPhones[$i];
            $phones[$i]['desc_ru'] = $dataPhones[$i+1];
            $phones[$i]['desc_uk'] = $dataPhones[$i+2];
        }

        $data = parent::fill($model, $request);

        $data['phones'] = array_values($phones);
        $data['location'] = $this->createPoint($request['lat'], $request['lon']);

        if(isset($data['brand_id']) && $data['brand_id'] == 0){
            $data['brand_id'] = null;
        }

        return $data;
    }

    /**
     * @param  Dealership  $model
     * @param  Request  $request
     */
    protected function afterSuccessfulSave($model, Request $request)
    {
        if(isset($request['schedule'])){
            $this->scheduleServices->createOrUpdate($request['schedule'], $model);
        }
    }

    /**
     * @param  Dealership $model
     * @param  array  $viewData
     * @return array
     */
    protected function createViewData($model, array $viewData): array
    {
        return [
            'cities' => $this->cityRepository->forSelect(),
            'selectedCity' => [],
            'brands' => $this->carBrandRepository->forSelect([], 'name','name', false, __('cms-cars::admin.Choice brand')),
            'selectedBrand' => [],
        ];
    }

    /**
     * @param  Dealership $model
     * @param  array  $viewData
     * @return array
     */
    protected function editViewData($model, array $viewData): array
    {
        return [
            'cities' => $this->cityRepository->forSelect(),
            'selectedCity' => [$model->city_id],
            'brands' => $this->carBrandRepository->forSelect([], 'name','name', false, __('cms-cars::admin.Choice brand')),
            'selectedBrand' => [$model->brand_id],
        ];
    }

}


