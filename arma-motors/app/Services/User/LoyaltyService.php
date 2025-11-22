<?php

namespace App\Services\User;

use App\DTO\User\LoyaltyDTO;
use App\DTO\User\LoyaltyEditDTO;
use App\Helpers\ConvertNumber;
use App\Models\User\Loyalty\Loyalty;
use App\Services\BaseService;
use DB;

class LoyaltyService extends BaseService
{
    public function __construct()
    {}

    public function create(LoyaltyDTO $dto): Loyalty
    {
        try {
            $model = new Loyalty();
            $model->brand_id = $dto->getBrandId();
            $model->type = $dto->getType();
            $model->age = $dto->getAge();
            $model->active = $dto->getActive();
            $model->discount = ConvertNumber::fromFloatToNumber($dto->getDiscount());

            $model->save();

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(LoyaltyEditDTO $dto, Loyalty $model): Loyalty
    {
        DB::beginTransaction();
        try {
            $model->discount = $dto->changeDiscount() ? ConvertNumber::fromFloatToNumber($dto->getDiscount()) : $model->discount;
            $model->active = $dto->getActive() ?? $model->active;
            $model->save();

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}


