<?php

namespace App\Services\Catalog;

use App\Dto\Catalog\ValueDto;
use App\Models\Catalog\Features\Value;
use App\Traits\Model\ToggleActive;
use Exception;
use Throwable;

class ValueService
{
    use ToggleActive;

    public function create(ValueDto $dto): Value
    {
        $model = new Value();

        $this->fill($dto, $model);
        $model->save();

        return $model;
    }

    private function fill(ValueDto $dto, Value $model): void
    {
        $model->title = $dto->getTitle();
        $model->feature_id = $dto->getFeatureId();
        $model->metric_id = $dto->getMetricId();
        $model->active = $dto->getActive();
        $model->sort = 1;
    }

    public function update(ValueDto $dto, Value $model): Value
    {
        $this->fill($dto, $model);
        $model->save();

        $model->refresh();

        return $model;
    }

    public function delete(Value $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }
}
