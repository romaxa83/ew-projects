<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Car\BrandEditDTO;
use App\Events\ChangeHashEvent;
use App\Models\Catalogs\Car\Brand;
use App\Models\Hash;

class BrandService
{
    public function __construct()
    {}

    public function edit(BrandEditDTO $dto, Brand $model): Brand
    {
        try {
            if($dto->changeColor()){
                $model->assetColor($dto->getColor());
            }

            $model->active = $dto->changeActive() ? $dto->getActive() : $model->active;
            $model->sort = $dto->changeSort() ? $dto->getSort() : $model->sort;
            $model->color = $dto->changeColor() ? $dto->getColor() : $model->color;
            $model->is_main = $dto->changeIsMain() ? $dto->getIsMain() : $model->is_main;
            $model->name = $dto->changeName() ? $dto->getName() : $model->name;
            $model->hourly_payment = $dto->changeHourlyPayment() ? $dto->getHourlyPayment() : $model->hourly_payment;
            $model->discount_hourly_payment = $dto->changeDiscountHourlyPayment()
                ? $dto->getDiscountHourlyPayment()
                : $model->discount_hourly_payment;

            $model->save();

            if(!$dto->emptyWorkIds()){
                $model->works()->detach();
                $model->works()->attach($dto->getWorkIds());
            }

            if(!$dto->emptyMileageIds()){
                $model->mileages()->detach();
                $model->mileages()->attach($dto->getMileageIds());
            }

            event(new ChangeHashEvent(Hash::ALIAS_BRAND));

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function toggleActive(Brand $model): Brand
    {
        try {
            $model->active = !$model->active;
            $model->save();

            event(new ChangeHashEvent(Hash::ALIAS_BRAND));

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function toggleMain(Brand $model): Brand
    {
        try {
            $model->is_main = !$model->is_main;
            $model->save();

            event(new ChangeHashEvent(Hash::ALIAS_BRAND));

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}




