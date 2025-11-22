<?php

namespace App\Services\TypeOfWorks;

use App\Dto\TypeOfWorks\TypeOfWorkDto;
use App\Dto\TypeOfWorks\TypeOfWorkInventoryDto;
use App\Models\TypeOfWorks\TypeOfWork;

class TypeOfWorkService
{
    public function __construct()
    {}

    public function create(TypeOfWorkDto $dto): TypeOfWork
    {
        return make_transaction(function() use($dto) {
            $model = $this->fill(new TypeOfWork(), $dto);
            if($work = TypeOfWork::query()->latest('id')->first()){
                $model->id = ++$work->id;
            }
            $model->save();

            foreach ($dto->inventories as $data) {
                /** @var $data TypeOfWorkInventoryDto */
                $model->inventories()->create([
                    'inventory_id' => $data->inventoryId,
                    'quantity' => $data->quantity,
                ]);
            }

            return $model;
        });
    }

    public function update(TypeOfWork $model, TypeOfWorkDto $dto): TypeOfWork
    {
        return make_transaction(function() use($model, $dto) {
            $model = $this->fill($model, $dto);
            $model->save();

            $updatedIds = [];
            foreach ($dto->inventories as $data) {
                /** @var $data TypeOfWorkInventoryDto */
                $inventory = $model->inventories()->updateOrCreate(
                    ['inventory_id' => $data->inventoryId],
                    ['quantity' => $data->quantity]
                );
                $updatedIds[] = $inventory->id;
            }

            $model->inventories()->whereNotIn('id', $updatedIds)->delete();

            return $model->refresh();
        });
    }

    protected function fill(TypeOfWork $model, TypeOfWorkDto $dto): TypeOfWork
    {
        $model->name = $dto->name;
        $model->duration = $dto->duration;
        $model->hourly_rate = $dto->hourlyRate;

        return $model;
    }

    public function delete(TypeOfWork $model): bool
    {
        return $model->delete();
    }
}

