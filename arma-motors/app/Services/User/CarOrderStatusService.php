<?php

namespace App\Services\User;

use App\DTO\User\CarOrderStatus\CarOrderStatusEditDTO;
use App\Models\User\OrderCar\OrderStatus;
use App\Services\BaseService;
use DB;

class CarOrderStatusService extends BaseService
{
    public function __construct()
    {}

    public function edit(CarOrderStatusEditDTO $dto, OrderStatus $model): OrderStatus
    {
        DB::beginTransaction();
        try {
            $model->sort = $dto->getSort() ?? $model->sort;
            $model->active = $dto->getActive() ?? $model->active;
            $model->save();

            $this->editTranslationsName($model, $dto);

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}



