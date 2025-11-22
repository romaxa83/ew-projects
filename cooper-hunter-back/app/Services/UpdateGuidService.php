<?php

namespace App\Services;

use App\Dto\ModelGuidsDto;
use App\Dto\UpdateGuidDto;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class UpdateGuidService
{
    /**
     * @throws Throwable
     */
    public function setGuids(ModelGuidsDto $guidsDto, Model|string $className): array
    {
        $response = [];

        $entities = $className::query()
            ->whereKey($guidsDto->getIds())
            ->get()
            ->keyBy('id');

        foreach ($guidsDto->getDto() as $guidDto) {
            $response[] = makeTransaction(
                function () use ($entities, $guidDto) {
                    return $this->updateGuid(
                        $entities->get($guidDto->getId()),
                        $guidDto
                    );
                }
            );
        }

        return $response;
    }

    public function updateGuid(Model $model, UpdateGuidDto $dto): Model
    {
        $model->guid = $dto->getGuid();
        $model->save();

        return $model;
    }
}
