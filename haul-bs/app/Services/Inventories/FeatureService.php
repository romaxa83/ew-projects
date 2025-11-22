<?php

namespace App\Services\Inventories;

use App\Dto\Inventories\FeatureDto;
use App\Events\Events\Inventories\Features\CreateFeatureEvent;
use App\Events\Events\Inventories\Features\DeleteFeatureEvent;
use App\Events\Events\Inventories\Features\UpdateFeatureEvent;
use App\Models\Inventories\Features\Feature;

final readonly class FeatureService
{
    public function __construct()
    {}

    public function create(FeatureDto $dto): Feature
    {
        $model = $this->fill(new Feature(), $dto);

        $model->save();

        event(new CreateFeatureEvent($model));

        return $model;
    }

    public function update(Feature $model, FeatureDto $dto): Feature
    {
        $model = $this->fill($model, $dto);

        $model->save();

        event(new UpdateFeatureEvent($model));

        return $model;
    }

    protected function fill(Feature $model, FeatureDto $dto): Feature
    {
        $model->name = $dto->name;
        $model->slug = $dto->slug;
        $model->position = $dto->position;
        $model->multiple = $dto->multiple;
        $model->active = $dto->active;

        return $model;
    }

    public function delete(Feature $model): bool
    {
        $clone = clone $model;
        $res = $model->delete();

        if ($res) event(new DeleteFeatureEvent($clone));

        return $res;
    }
}
