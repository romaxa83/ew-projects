<?php

namespace App\Services\Inventories;

use App\Dto\Inventories\FeatureValueDto;
use App\Events\Events\Inventories\FeatureValues\CreateFeatureValueEvent;
use App\Events\Events\Inventories\FeatureValues\DeleteFeatureValueEvent;
use App\Events\Events\Inventories\FeatureValues\UpdateFeatureValueEvent;
use App\Models\Inventories\Features\Value;

final readonly class FeatureValueService
{
    public function __construct()
    {}

    public function create(FeatureValueDto $dto): Value
    {
        $model = $this->fill(new Value(), $dto);
        $model->feature_id = $dto->featureId;

        $model->save();

        event(new CreateFeatureValueEvent($model));

        return $model;
    }

    public function update(Value $model, FeatureValueDto $dto): Value
    {
        $model = $this->fill($model, $dto);

        $model->save();

        event(new UpdateFeatureValueEvent($model));

        return $model;
    }

    protected function fill(Value $model, FeatureValueDto $dto): Value
    {
        $model->name = $dto->name;
        $model->slug = $dto->slug;
        $model->position = $dto->position;
        $model->active = $dto->active;

        return $model;
    }

    public function delete(Value $model): bool
    {
        $clone = clone $model;
        $res = $model->delete();
//        $res = true;

        if ($res) event(new DeleteFeatureValueEvent($clone));

        return $res;
    }
}
