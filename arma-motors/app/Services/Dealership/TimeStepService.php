<?php

namespace App\Services\Dealership;

use App\DTO\Dealership\TimeStepDTO;
use App\Models\Dealership\Dealership;
use App\Models\Dealership\TimeStep;
use App\Services\BaseService;

class TimeStepService extends BaseService
{
    public function __construct()
    {}

    public function create(TimeStepDTO $dto, Dealership $dealership): TimeStep
    {
        try {
            $model = new TimeStep();
            $model->dealership_id = $dealership->id;
            $model->service_id = $dto->serviceId;
            $model->step = $dto->step;

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit()
    {
        \DB::beginTransaction();
        try {

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

