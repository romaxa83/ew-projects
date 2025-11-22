<?php

namespace Tests\_Helpers;

use App\Models\Catalogs\Calc\CalcModel;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Calc\Spares;
use App\Models\Catalogs\Calc\Work;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\DriveUnit;
use App\Models\Catalogs\Car\EngineVolume;
use App\Models\Catalogs\Car\Fuel;
use App\Models\Catalogs\Car\Model;
use App\Models\Catalogs\Car\Transmission;
use App\Models\Dealership\Department;
use App\Models\Promotion\Promotion;
use Database\Factories\Promotion\PromotionTranslationFactory;

class PromotionBuilder
{
    private $departmentId;
    private $type;
    private $active = true;
    private $userIds = [];

    // BrandID
    public function getDepartmentId()
    {
        if(null == $this->departmentId){
            $this->setDepartmentId(Department::select('id')->orderBy(\DB::raw('RAND()'))->first()->id);
        }

        return $this->departmentId;
    }
    public function setDepartmentId(int $departmentId): self
    {
        $this->departmentId = $departmentId;

        return $this;
    }

    public function getType()
    {
        if(null == $this->type){
            $this->setType(Promotion::TYPE_COMMON);
        }

        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getActive()
    {
        return $this->active;
    }
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function setUsersId(array $ids): self
    {
        $this->userIds = $ids;

        return $this;
    }

    public function create()
    {
        $model = $this->save();

        PromotionTranslationFactory::new(['model_id' => $model->id])->create(['lang' => 'ru']);
        PromotionTranslationFactory::new(['model_id' => $model->id])->create(['lang' => 'uk']);

        $model->users()->attach($this->userIds);

        $this->clear();

        return $model;
    }

    private function save()
    {
        $data = [
            'department_id' => $this->getDepartmentId(),
            'type' => $this->getType(),
            'active' => $this->getActive(),
        ];

        return Promotion::factory()->new($data)->create();
    }
    private function clear()
    {
        $this->departmentId = null;
        $this->type = null;
        $this->userIds = [];
        $this->active = true;
    }
}


