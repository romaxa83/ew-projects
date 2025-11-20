<?php

namespace WezomCms\Regions\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\Repositories\DealershipRepository;
use WezomCms\Dealerships\DTO\DealershipDto;
use WezomCms\Regions\DTO\CityListDto;
use WezomCms\Regions\Repositories\CityRepository;

class CityController extends ApiController
{
    private CityRepository $cityRepository;
    private CityListDto $cityListDto;

    public function __construct(CityRepository $cityRepository)
    {
        parent::__construct();
        $this->cityRepository = $cityRepository;
        $this->cityListDto = resolve(CityListDto::class);
    }

    public function list(Request $request)
    {
        try {
            $cities = $this->cityRepository->getAll(['translations', 'dealership'], 'sort', $request->all());

            return $this->successJsonMessage(
                $this->cityListDto
                    ->setCount($this->cityRepository->countByRequest($request->all()))
                    ->setCollection($cities)
                    ->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }
}

