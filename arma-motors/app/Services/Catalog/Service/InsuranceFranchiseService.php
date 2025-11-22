<?php

namespace App\Services\Catalog\Service;

use App\DTO\Catalog\Service\InsuranceFranchiseDTO;
use App\DTO\Catalog\Service\InsuranceFranchiseEditDTO;
use App\Models\Catalogs\Service\InsuranceFranchise;
use App\Models\Catalogs\Service\Service;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Services\BaseService;
use DB;

class InsuranceFranchiseService extends BaseService
{
    public function __construct(protected ServiceRepository $serviceRepository)
    {}

    public function create(InsuranceFranchiseDTO $dto): InsuranceFranchise
    {
        DB::beginTransaction();
        try {

            $model = new InsuranceFranchise();
            $model->sort = $dto->getSort();
            $model->active = $dto->getActive();
            $model->name = $dto->getName();

            $model->save();

            // привязываем к сервисам
            if(!$dto->emptyInsuranceIds()){
                // привязываем только те сервисы у которых родитель является страховкой
                foreach ($dto->getInsuranceIds() as $id){
                    /** @var $service Service */
                    $service = $this->serviceRepository->getByID($id, ['parent']);
                    if(null != $service->parent && $service->parent->isInsurance()){
                        $model->insurances()->attach($service);
                    }
                }
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(InsuranceFranchiseEditDTO $dto, InsuranceFranchise $model): InsuranceFranchise
    {
        DB::beginTransaction();
        try {

            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->name = $dto->getName() ?? $model->name;
            $model->save();

            // привязываем к сервисам
            if(!$dto->emptyInsuranceIds()){
                $model->insurances()->detach();
                // привязываем только те сервисы у которых родитель является страховкой
                foreach ($dto->getInsuranceIds() as $id){
                    /** @var $service Service */
                    $service = $this->serviceRepository->getByID($id, ['parent']);
                    if(null != $service->parent && $service->parent->isInsurance()){
                        $model->insurances()->attach($service);
                    }
                }
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

