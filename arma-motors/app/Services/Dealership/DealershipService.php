<?php

namespace App\Services\Dealership;

use App\DTO\Dealership\DealershipDTO;
use App\DTO\Dealership\DealershipTranslationDTO;
use App\DTO\Dealership\DepartmentDTO;
use App\Events\ChangeHashEvent;
use App\Models\Dealership\Dealership;
use App\Models\Dealership\DealershipTranslation;
use App\Models\Dealership\Department;
use App\Models\Hash;
use App\Services\BaseService;

class DealershipService extends BaseService
{
    public function __construct(
        protected DepartmentService $departmentService,
        protected TimeStepService $timeStepService,
    )
    {}

    public function create(DealershipDTO $dto): Dealership
    {
        \DB::beginTransaction();
        try {
            $model = new Dealership();
            $model->active = $dto->getActive();
            $model->sort = $dto->getSort();
            $model->brand_id = $dto->getBrandId();
            $model->location = $dto->getLocation();
            $model->website = $dto->getWebsite();
            $model->alias = $dto->getAlias();

            $model ->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation DealershipTranslationDTO */
                $t = new DealershipTranslation();
                $t->dealership_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->text = $translation->getText();
                $t->address = $translation->getAddress();
                $t->save();
            }

            foreach ($dto->getTimeStep() as $step){
                $this->timeStepService->create($step, $model);
            }

            if($dto->hasDepartments()){
                /** @var $department DepartmentDTO */
                foreach ($dto->getDepartments() as $department){
                    $this->departmentService->create($department, $model->id);
                }
            }

            event(new ChangeHashEvent(Hash::ALIAS_DEALERSHIP));

            \DB::commit();
            return $model;
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(DealershipDTO $dto,Dealership $model): Dealership
    {
        \DB::beginTransaction();
        try {
            $model->active = $dto->getActive();
            $model->sort = $dto->getSort();
            $model->brand_id = $dto->getBrandId();
            $model->location = $dto->getLocation();
            $model->website = $dto->getWebsite();

            $model ->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation DealershipTranslationDTO */
                $t = $model->translations()->where('lang', $translation->getLang())->first();
                $t->name = $translation->getName();
                $t->text = $translation->getText();
                $t->address = $translation->getAddress();
                $t->save();
            }

            $model->timeStep()->delete();
            foreach ($dto->getTimeStep() as $step){
                $this->timeStepService->create($step, $model);
            }

            if($dto->hasDepartments()){
                /** @var $department DepartmentDTO */
                foreach ($dto->getDepartments() as $department){
                    /** @var $d Department */
                    $d = $model->departments()->where('type', $department->getType())->first();

                    $this->departmentService->edit($department, $d);
                }
            }

            event(new ChangeHashEvent(Hash::ALIAS_DEALERSHIP));

            \DB::commit();
            return $model;
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
