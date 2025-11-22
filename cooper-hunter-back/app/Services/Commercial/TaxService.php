<?php

namespace App\Services\Commercial;

use App\Dto\Commercial\TaxDto;
use App\Dto\Commercial\TaxesDto;
use App\Models\Commercial\Tax;

class TaxService
{
    public function __construct()
    {}

    public function createOrUpdate(TaxesDto $dto)
    {
        foreach ($dto->items as $item) {
            /** @var $item TaxDto */
            if($model = $this->getModelByGuid($item->guid)){
                $this->update($model, $item);
            } else {
                $this->create($item);
            }
        }
    }

    public function create(TaxDto $dto): Tax
    {
        $model = new Tax();
        $model->guid = $dto->guid;
        $model->name = $dto->name;
        $model->value = $dto->value;

        $model->save();

        return $model;
    }

    public function update(Tax $model, TaxDto $dto): Tax
    {
        $model->name = $dto->name;
        $model->value = $dto->value;

        $model->save();

        return $model;
    }

    public function getModelByGuid($guid):? Tax
    {
        return Tax::query()->where('guid', $guid)->first();
    }

    public function removeByGuids(array $data): int
    {
        $count = 0;
        foreach ($data ?? [] as $guid) {
            if($model = $this->getModelByGuid($guid)){
                $model->delete();
                $count++;
            }
        }

        return $count;
    }

}

