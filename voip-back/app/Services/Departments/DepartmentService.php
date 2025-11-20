<?php

namespace App\Services\Departments;

use App\Dto\Departments\DepartmentDto;
use App\IPTelephony\Events\QueueMember\QueueMemberUpdateNameEvent;
use App\Models\Departments\Department;
use App\Repositories\Departments\DepartmentRepository;
use App\Services\AbstractService;
use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;

class DepartmentService extends AbstractService
{
    public function __construct()
    {
        $this->repo = resolve(DepartmentRepository::class);
        return parent::__construct();
    }

    public function create(DepartmentDto $dto): Department
    {
        $model = new Department();

        $model->guid = Uuid::uuid4();
        $model->num = $this->getUniqNum();
        $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    public function getUniqNum(): int
    {
        $num = substr(CarbonImmutable::now()->timestamp, -5);

        if($this->repo->existBy(['num' => $num])){
            self::getUniqNum();
        }

        return $num;
    }

    public function update(Department $model, DepartmentDto $dto): Department
    {
        $oldName = null;
        if($model->name !== $dto->name){
            $oldName = $model->name;
        }

        $this->fill($model, $dto);

        if($model->save() && $oldName){
            event(new QueueMemberUpdateNameEvent($oldName, $dto->name));
        }

        $model->save();

        return $model;
    }

    protected function fill(Department $model, DepartmentDto $dto): void
    {
        $model->name = $dto->name;
        $model->active = $dto->active;
    }
}
