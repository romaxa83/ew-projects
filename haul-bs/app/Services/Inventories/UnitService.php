<?php

namespace App\Services\Inventories;

use App\Dto\Inventories\UnitDto;
use App\Models\Inventories\Unit;

final readonly class UnitService
{
    public function __construct()
    {}

    public function create(UnitDto $dto): Unit
    {
        $model = $this->fill(new Unit(), $dto);

        $model->save();

        return $model;
    }

    public function update(Unit $model, UnitDto $dto): Unit
    {
        $model = $this->fill($model, $dto);

        $model->save();

        return $model;
    }

    protected function fill(Unit $model, UnitDto $dto): Unit
    {
        $model->name = $dto->name;
        $model->accept_decimals = $dto->acceptDecimals;

        return $model;
    }

    public function delete(Unit $model): bool
    {
        return $model->delete();
    }
}
