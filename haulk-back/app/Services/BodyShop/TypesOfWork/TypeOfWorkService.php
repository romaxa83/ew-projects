<?php

namespace App\Services\BodyShop\TypesOfWork;

use App\Dto\BodyShop\TypesOfWork\TypeOfWorkDto;
use App\Models\BodyShop\TypesOfWork\TypeOfWork;
use DB;
use Exception;
use Log;

class TypeOfWorkService
{
    public function create(TypeOfWorkDto $dto): TypeOfWork
    {
        try {
            DB::beginTransaction();

            /** @var TypeOfWork $typeOfWork */
            $typeOfWork = TypeOfWork::query()->make($dto->getTypeOfWorkData());
            $typeOfWork->saveOrFail();
            foreach ($dto->getInventoriesData() as $data) {
                $typeOfWork->inventories()->create([
                    'inventory_id' => $data['id'],
                    'quantity' => $data['quantity'],
                ]);
            }

            DB::commit();

            return $typeOfWork;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(TypeOfWork $typeOfWork, TypeOfWorkDto $dto): TypeOfWork
    {
        try {
            DB::beginTransaction();

            $typeOfWork->update($dto->getTypeOfWorkData());
            $updatedIds = [];
            foreach ($dto->getInventoriesData() as $data) {
                $inventory = $typeOfWork->inventories()->updateOrCreate(
                    ['inventory_id' => $data['id']],
                    ['quantity' => $data['quantity']]
                );
                $updatedIds[] = $inventory->id;
            }

            $typeOfWork->inventories()->whereNotIn('id', $updatedIds)->delete();

            DB::commit();

            return $typeOfWork;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(TypeOfWork $type): TypeOfWork
    {
        $type->delete();

        return $type;
    }
}
