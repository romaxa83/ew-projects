<?php

namespace WezomCms\Services\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Traits\CoordsTrait;
use WezomCms\Dealerships\Repositories\DealershipRepository;
use WezomCms\Dealerships\DTO\DealershipDto;
use WezomCms\Regions\DTO\CityListDto;
use WezomCms\Regions\Repositories\CityRepository;
use WezomCms\Requests\Services\RequestsServices;
use WezomCms\Services\DTO\InsuranceListDto;
use WezomCms\Services\DTO\StoServiceListDto;
use WezomCms\Services\Repositories\ServiceGroupRepository;
use WezomCms\Services\Types\ServiceType;

class ServiceController extends ApiController
{
    private ServiceGroupRepository $serviceGroupRepository;
    private StoServiceListDto $stoServiceListDto;
    private InsuranceListDto $insuranceListDto;

    public function __construct(ServiceGroupRepository $serviceGroupRepository)
    {
        parent::__construct();
        $this->serviceGroupRepository = $serviceGroupRepository;
        $this->stoServiceListDto = \App::make(StoServiceListDto::class);
        $this->insuranceListDto = \App::make(InsuranceListDto::class);
    }

    public function listSto()
    {
        try {
            $services = $this->serviceGroupRepository->getServicesByType(ServiceType::getTypeSto(), [
                'services' => function($q){
                    return $q->orderBy('sort');
                }
            ]);

            return $this->successJsonMessage(
                $this->stoServiceListDto
                    ->setCollection($services)
                    ->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

    public function listInsurance()
    {
        try {
            $services = $this->serviceGroupRepository->getServicesByType(ServiceType::getTypeInsurance(), [
                'services' => function($q){
                    return $q->orderBy('sort');
                }
            ]);

            return $this->successJsonMessage(
                $this->insuranceListDto
                    ->setCollection($services)
                    ->toList()
            );
        } catch(\Exception $exception){
            return $this->errorJsonMessage($exception->getMessage());
        }
    }

}
