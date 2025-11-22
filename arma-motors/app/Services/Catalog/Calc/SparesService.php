<?php

namespace App\Services\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesEditDTO;
use App\Helpers\ConvertNumber;
use App\Models\Catalogs\Calc\Spares;
use App\Services\BaseService;
use App\ValueObjects\Money;
use DB;

class SparesService extends BaseService
{

    public function __construct()
    {}

    public function edit(SparesEditDTO $dto, Spares $model): Spares
    {
        DB::beginTransaction();
        try {

            $model->group_id = $dto->getGroupId() ?? $model->group_id;
            $model->active = $dto->getActive() ?? $model->active;
            $model->name = $dto->getName() ?? $model->name;
            $model->price = $dto->changePrice() ? new Money($dto->getPrice()) : $model->price;
            $model->discount_price = $dto->changePriceDiscount()
                ? null != $dto->getPriceDiscount() ? new Money($dto->getPriceDiscount()) : null
                : $model->discount_price;

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
